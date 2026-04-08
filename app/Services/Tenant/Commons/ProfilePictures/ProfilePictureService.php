<?php

declare(strict_types=1);

namespace App\Services\Tenant\Commons\ProfilePictures;

use App\Services\Tenant\Commons\Files\TenantAssetPathBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
use RuntimeException;

class ProfilePictureService
{
    private const int MAX_DIMENSION = 800;

    private const int WEBP_QUALITY = 80;

    public function __construct(
        private readonly TenantAssetPathBuilder $assetPaths,
    ) {}

    /**
     * Process an uploaded image (resize + WebP) and store it to disk.
     *
     * @return string The storage path of the saved picture.
     */
    public function store(UploadedFile $file, string $module, string $entityId): string
    {
        $realPath = $file->getRealPath();

        if ($realPath === false) {
            throw new RuntimeException('Uploaded file has no readable path on disk.');
        }

        $image = Image::decode($realPath);

        if ($image->width() > self::MAX_DIMENSION || $image->height() > self::MAX_DIMENSION) {
            $image->scale(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);
        }

        $encoded = $image->encode(new WebpEncoder(self::WEBP_QUALITY));

        $path = $this->buildPath(module: $module, entityId: $entityId);
        $disk = Storage::disk($this->assetPaths->defaultDisk());

        $written = $disk->put($path, $encoded->toString(), [
            'mimetype' => 'image/webp',
        ]);

        if (! $written) {
            throw new RuntimeException("Failed to write profile picture to [{$path}].");
        }

        return $path;
    }

    /**
     * Delete a profile picture from disk.
     */
    public function delete(string $path): void
    {
        Storage::disk($this->assetPaths->defaultDisk())->delete($path);
    }

    /**
     * Replace an existing profile picture: store new, then delete old.
     *
     * @return string The storage path of the new picture.
     */
    public function replace(?string $currentPath, UploadedFile $file, string $module, string $entityId): string
    {
        $newPath = $this->store($file, $module, $entityId);

        if ($currentPath !== null && $currentPath !== '' && $currentPath !== $newPath) {
            $this->delete($currentPath);
        }

        return $newPath;
    }

    /**
     * Sync a profile picture for a model that uses the HasProfilePicture trait.
     *
     * When $deleteOnNull is false (create flow): null means "no upload, skip".
     * When $deleteOnNull is true (update flow): null means "remove existing picture".
     */
    public function sync(Model $entity, ?UploadedFile $file, bool $deleteOnNull = false): void
    {
        /** @var MorphOne $relation */
        $relation = $entity->profilePicture();
        $existing = $relation->first();

        if ($file === null) {
            if ($deleteOnNull && $existing) {
                $oldPath = $existing->path;
                $existing->delete();

                try {
                    $this->delete($oldPath);
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            return;
        }

        $newPath = $this->replace(
            $existing?->path,
            $file,
            $entity->getMorphClass(),
            (string) $entity->getKey(),
        );

        $relation->updateOrCreate([], ['path' => $newPath]);
    }

    private function buildPath(string $module, string $entityId): string
    {
        return $this->assetPaths->buildPath(module: 'profile-images', ids: $module, type: $entityId.'.webp');
    }
}
