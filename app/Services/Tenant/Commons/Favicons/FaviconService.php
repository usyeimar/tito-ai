<?php

namespace App\Services\Tenant\Commons\Favicons;

use App\Models\Tenant\Commons\EntityFavicon;
use App\Services\Tenant\Commons\Files\TenantAssetPathBuilder;
use DOMDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FaviconService
{
    /** Maximum allowed favicon size in bytes (512 KB). */
    private const MAX_FAVICON_BYTES = 512 * 1024;

    private const ALLOWED_IMAGE_MIMES = [
        'image/png',
        'image/gif',
        'image/jpeg',
        'image/webp',
        'image/vnd.microsoft.icon',
        'image/x-icon',
    ];

    public function __construct(
        private readonly TenantAssetPathBuilder $assetPaths,
    ) {}

    public function sync(Model $entity, string $website): ?EntityFavicon
    {
        $normalizedWebsite = $this->normalizeWebsite($website);

        if ($normalizedWebsite === null) {
            return null;
        }

        $faviconUrl = $this->discoverFaviconUrl($normalizedWebsite);

        if ($faviconUrl === null) {
            throw new RuntimeException('Unable to determine favicon URL.');
        }

        $downloaded = $this->downloadFavicon($faviconUrl);
        $path = $this->store($entity, $downloaded['contents'], $downloaded['mime_type'], $downloaded['extension']);

        $existing = $entity->favicon;

        $favicon = $entity->favicon()->updateOrCreate([], [
            'path' => $path,
            'mime_type' => $downloaded['mime_type'],
        ]);

        if ($existing && $existing->path !== $path) {
            $this->deletePath($existing->path);
        }

        return $favicon;
    }

    public function clear(Model $entity): void
    {
        $favicon = $entity->favicon;

        if (! $favicon instanceof EntityFavicon) {
            return;
        }

        $path = $favicon->path;
        $favicon->delete();

        $this->deletePath($path);
    }

    private function normalizeWebsite(string $website): ?string
    {
        $candidate = trim($website);

        if ($candidate === '') {
            return null;
        }

        if (! str_contains($candidate, '://')) {
            $candidate = 'https://'.$candidate;
        }

        if (filter_var($candidate, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $host = parse_url($candidate, PHP_URL_HOST);

        if (! is_string($host) || ! $this->isSafeHost($host)) {
            return null;
        }

        return $candidate;
    }

    private function isSafeHost(string $host): bool
    {
        $lower = strtolower($host);

        if (in_array($lower, ['localhost', '0.0.0.0', ''], true)) {
            return false;
        }

        if (str_ends_with($lower, '.local') || str_ends_with($lower, '.internal')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        }

        return true;
    }

    private function discoverFaviconUrl(string $website): ?string
    {
        try {
            $response = Http::timeout(10)
                ->accept('text/html,application/xhtml+xml')
                ->get($website);

            if ($response->successful()) {
                $html = (string) $response->body();

                foreach ($this->extractIconCandidates($html) as $candidate) {
                    $resolved = $this->resolveUrl($website, $candidate);

                    if ($resolved !== null) {
                        return $resolved;
                    }
                }
            }
        } catch (\Throwable) {
        }

        $origin = $this->origin($website);

        return $origin ? rtrim($origin, '/').'/favicon.ico' : null;
    }

    /**
     * @return array<int, string>
     */
    private function extractIconCandidates(string $html): array
    {
        if ($html === '') {
            return [];
        }

        $dom = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        try {
            if (! $dom->loadHTML($html)) {
                return [];
            }

            $candidates = [];

            foreach ($dom->getElementsByTagName('link') as $link) {
                $rel = strtolower((string) $link->getAttribute('rel'));

                if (! str_contains($rel, 'icon')) {
                    continue;
                }

                $href = trim((string) $link->getAttribute('href'));

                if ($href !== '') {
                    $candidates[] = $href;
                }
            }

            return array_values(array_unique($candidates));
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /**
     * @return array{contents:string,mime_type:string,extension:string}
     */
    private function downloadFavicon(string $url): array
    {
        $response = Http::timeout(10)
            ->withOptions(['stream' => true])
            ->withHeaders(['Accept' => 'image/*,*/*;q=0.8'])
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Favicon request failed with status '.$response->status().'.');
        }

        $contents = (string) $response->body();

        if ($contents === '') {
            throw new RuntimeException('Favicon response body was empty.');
        }

        if (strlen($contents) > self::MAX_FAVICON_BYTES) {
            throw new RuntimeException('Favicon exceeds maximum allowed size of '.self::MAX_FAVICON_BYTES.' bytes.');
        }

        $mimeType = strtolower(trim(strtok((string) $response->header('Content-Type', ''), ';')));

        if ($mimeType === '' || ! str_starts_with($mimeType, 'image/')) {
            $mimeType = (string) (finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $contents) ?: 'application/octet-stream');
        }

        if (! in_array($mimeType, self::ALLOWED_IMAGE_MIMES, true)) {
            throw new RuntimeException("Favicon has unsupported MIME type: {$mimeType}.");
        }

        $extension = $this->extensionForMimeType($mimeType);

        return [
            'contents' => $contents,
            'mime_type' => $mimeType,
            'extension' => $extension,
        ];
    }

    private function store(Model $entity, string $contents, string $mimeType, string $extension): string
    {
        $path = $this->assetPaths->buildPath(
            module: 'favicons',
            ids: $entity->getMorphClass(),
            type: (string) $entity->getKey(),
            file: 'favicon.'.$extension,
        );

        $written = Storage::disk($this->assetPaths->defaultDisk())->put($path, $contents, [
            'ContentType' => $mimeType,
        ]);

        if (! $written) {
            throw new RuntimeException('Failed to write favicon asset.');
        }

        return $path;
    }

    private function deletePath(?string $path): void
    {
        if (! is_string($path) || $path === '') {
            return;
        }

        Storage::disk($this->assetPaths->defaultDisk())->delete($path);
    }

    private function extensionForMimeType(string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            'image/vnd.microsoft.icon', 'image/x-icon' => 'ico',
            default => 'bin',
        };
    }

    private function origin(string $url): ?string
    {
        $parts = parse_url($url);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $origin = $parts['scheme'].'://'.$parts['host'];

        if (isset($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }

        return $origin;
    }

    private function resolveUrl(string $baseUrl, string $candidate): ?string
    {
        if ($candidate === '') {
            return null;
        }

        $resolved = $this->buildResolvedUrl($baseUrl, $candidate);

        if ($resolved === null) {
            return null;
        }

        $host = parse_url($resolved, PHP_URL_HOST);

        if (! is_string($host) || ! $this->isSafeHost($host)) {
            return null;
        }

        return $resolved;
    }

    private function buildResolvedUrl(string $baseUrl, string $candidate): ?string
    {
        if (str_starts_with($candidate, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME);

            return ($scheme ?: 'https').':'.$candidate;
        }

        if (filter_var($candidate, FILTER_VALIDATE_URL) !== false) {
            return $candidate;
        }

        $origin = $this->origin($baseUrl);

        if ($origin === null) {
            return null;
        }

        if (str_starts_with($candidate, '/')) {
            return rtrim($origin, '/').$candidate;
        }

        $basePath = (string) parse_url($baseUrl, PHP_URL_PATH);
        $directory = Str::beforeLast($basePath === '' ? '/' : $basePath, '/');
        $directory = $directory === '' ? '/' : $directory.'/';

        return rtrim($origin, '/').$directory.ltrim($candidate, '/');
    }
}
