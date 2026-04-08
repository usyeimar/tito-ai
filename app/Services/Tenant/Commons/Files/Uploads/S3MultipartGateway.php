<?php

namespace App\Services\Tenant\Commons\Files\Uploads;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use RuntimeException;

class S3MultipartGateway implements MultipartGateway
{
    public function initiate(string $disk, string $objectKey, string $mimeType, string $originalFilename): string
    {
        $client = $this->client($disk);
        $bucket = $this->bucket($disk);
        $providerObjectKey = $this->providerObjectKey($disk, $objectKey);

        $result = $client->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $providerObjectKey,
            'ContentType' => $mimeType,
            'ContentDisposition' => 'inline; filename="'.$originalFilename.'"',
        ]);

        $uploadId = $result['UploadId'] ?? null;

        if (! is_string($uploadId) || $uploadId === '') {
            throw new RuntimeException('Unable to start multipart upload.');
        }

        return $uploadId;
    }

    public function signPart(string $disk, string $objectKey, string $uploadId, int $partNumber, int $expiresInSeconds): array
    {
        $client = $this->client($disk, true);
        $bucket = $this->bucket($disk);
        $providerObjectKey = $this->providerObjectKey($disk, $objectKey);

        $command = $client->getCommand('UploadPart', [
            'Bucket' => $bucket,
            'Key' => $providerObjectKey,
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
        ]);

        $request = $client->createPresignedRequest($command, "+{$expiresInSeconds} seconds");

        return [
            'url' => (string) $request->getUri(),
            'headers' => [
                'Content-Type' => 'application/octet-stream',
            ],
            'expires_at' => now()->addSeconds($expiresInSeconds)->toISOString(),
        ];
    }

    public function complete(string $disk, string $objectKey, string $uploadId, array $parts): void
    {
        $client = $this->client($disk);
        $bucket = $this->bucket($disk);
        $providerObjectKey = $this->providerObjectKey($disk, $objectKey);

        $mappedParts = array_map(static fn (array $part): array => [
            'PartNumber' => $part['part_number'],
            'ETag' => $part['etag'],
        ], $parts);

        $client->completeMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $providerObjectKey,
            'UploadId' => $uploadId,
            'MultipartUpload' => [
                'Parts' => $mappedParts,
            ],
        ]);
    }

    public function abort(string $disk, string $objectKey, string $uploadId): void
    {
        $client = $this->client($disk);
        $bucket = $this->bucket($disk);
        $providerObjectKey = $this->providerObjectKey($disk, $objectKey);

        try {
            $client->abortMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $providerObjectKey,
                'UploadId' => $uploadId,
            ]);
        } catch (AwsException $exception) {
            if ($this->isNoSuchUpload($exception) || $exception->getStatusCode() === 404) {
                return;
            }

            throw $exception;
        }
    }

    public function listUploadedParts(string $disk, string $objectKey, string $uploadId): array
    {
        $client = $this->client($disk);
        $bucket = $this->bucket($disk);
        $providerObjectKey = $this->providerObjectKey($disk, $objectKey);
        $parts = [];
        $partNumberMarker = null;

        do {
            $payload = [
                'Bucket' => $bucket,
                'Key' => $providerObjectKey,
                'UploadId' => $uploadId,
            ];

            if ($partNumberMarker !== null) {
                $payload['PartNumberMarker'] = $partNumberMarker;
            }

            try {
                $result = $client->listParts($payload);
            } catch (AwsException $exception) {
                if ($this->isNoSuchUpload($exception)) {
                    throw new MultipartUploadNotFoundException(
                        'Multipart upload does not exist in storage provider.',
                        0,
                        $exception,
                    );
                }

                throw $exception;
            }

            $resultParts = $result['Parts'] ?? [];

            foreach ($resultParts as $part) {
                $partNumber = (int) ($part['PartNumber'] ?? 0);
                $etag = trim((string) ($part['ETag'] ?? ''), '"');
                $size = (int) ($part['Size'] ?? 0);

                if ($partNumber < 1 || $etag === '') {
                    continue;
                }

                $parts[] = [
                    'part_number' => $partNumber,
                    'etag' => $etag,
                    'size' => $size,
                ];
            }

            $partNumberMarker = isset($result['NextPartNumberMarker'])
                ? (int) $result['NextPartNumberMarker']
                : null;
        } while ((bool) ($result['IsTruncated'] ?? false));

        return $parts;
    }

    private function client(string $disk, bool $forPresign = false): S3Client
    {
        $config = (array) config("filesystems.disks.{$disk}", []);

        if (($config['driver'] ?? null) !== 's3') {
            throw new RuntimeException('Multipart uploads require an s3-compatible disk.');
        }

        $clientConfig = [
            'version' => 'latest',
            'region' => (string) ($config['region'] ?? config('filesystems.disks.s3.region', 'us-east-1')),
            'endpoint' => $forPresign
                ? $this->presignEndpoint($config)
                : $this->internalEndpoint($config),
            'use_path_style_endpoint' => (bool) ($config['use_path_style_endpoint'] ?? false),
        ];

        $key = (string) ($config['key'] ?? '');
        $secret = (string) ($config['secret'] ?? '');

        if ($key !== '' && $secret !== '') {
            $clientConfig['credentials'] = [
                'key' => $key,
                'secret' => $secret,
            ];
        }

        return new S3Client($clientConfig);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function internalEndpoint(array $config): ?string
    {
        $endpoint = $config['endpoint'] ?? null;

        if (! is_string($endpoint)) {
            return null;
        }

        $endpoint = trim($endpoint);

        return $endpoint !== '' ? $endpoint : null;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function presignEndpoint(array $config): ?string
    {
        $presignEndpoint = $this->endpointFromUrl($config['presign_endpoint'] ?? null);

        if ($presignEndpoint !== null) {
            return $presignEndpoint;
        }

        $urlEndpoint = $this->endpointFromUrl($config['url'] ?? null);

        if ($urlEndpoint !== null) {
            if ($this->shouldUseApplicationEndpoint($urlEndpoint)) {
                return $this->applicationEndpoint() ?? $urlEndpoint;
            }

            return $urlEndpoint;
        }

        return $this->internalEndpoint($config);
    }

    private function applicationEndpoint(): ?string
    {
        return $this->endpointFromUrl(config('app.url'));
    }

    private function shouldUseApplicationEndpoint(string $endpoint): bool
    {
        if (! app()->environment('local')) {
            return false;
        }

        $parsed = parse_url($endpoint);
        $host = is_array($parsed) ? ($parsed['host'] ?? null) : null;

        if (! is_string($host) || $host === '') {
            return false;
        }

        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return false;
        }

        return ! str_contains($host, '.');
    }

    private function endpointFromUrl(mixed $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $parsed = parse_url($url);
        $scheme = is_array($parsed) ? ($parsed['scheme'] ?? null) : null;
        $host = is_array($parsed) ? ($parsed['host'] ?? null) : null;
        $port = is_array($parsed) ? ($parsed['port'] ?? null) : null;

        if (is_string($scheme) && $scheme !== '' && is_string($host) && $host !== '') {
            return $port !== null
                ? sprintf('%s://%s:%d', $scheme, $host, (int) $port)
                : sprintf('%s://%s', $scheme, $host);
        }

        return null;
    }

    private function providerObjectKey(string $disk, string $objectKey): string
    {
        $normalizedKey = ltrim(trim($objectKey), '/');
        $root = trim((string) config("filesystems.disks.{$disk}.root", ''), '/');

        if ($root === '' || $normalizedKey === '' || $normalizedKey === $root || str_starts_with($normalizedKey, $root.'/')) {
            return $normalizedKey;
        }

        return $root.'/'.$normalizedKey;
    }

    private function isNoSuchUpload(AwsException $exception): bool
    {
        return $exception->getAwsErrorCode() === 'NoSuchUpload';
    }

    private function bucket(string $disk): string
    {
        $bucket = (string) config("filesystems.disks.{$disk}.bucket", '');

        if ($bucket === '') {
            throw new RuntimeException('S3 bucket is not configured for selected disk.');
        }

        return $bucket;
    }
}
