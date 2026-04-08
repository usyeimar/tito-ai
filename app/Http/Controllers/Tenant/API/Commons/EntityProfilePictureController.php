<?php

namespace App\Http\Controllers\Tenant\API\Commons;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Commons\EntityProfilePicture;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class EntityProfilePictureController extends Controller
{
    public function show(EntityProfilePicture $entityProfilePicture): Response
    {
        $entity = $entityProfilePicture->entity;

        abort_if($entity === null, 404, 'Profile picture owner not found.');

        Gate::authorize('view', $entity);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('filesystems.default'));

        if (! $disk->exists($entityProfilePicture->path)) {
            abort(404, 'File not found.');
        }

        return $disk->response($entityProfilePicture->path, null, [
            'Content-Type' => 'image/webp',
        ]);
    }
}
