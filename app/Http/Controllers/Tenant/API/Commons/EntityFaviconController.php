<?php

namespace App\Http\Controllers\Tenant\API\Commons;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Commons\EntityFavicon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class EntityFaviconController extends Controller
{
    public function show(EntityFavicon $entityFavicon): Response
    {
        $entity = $entityFavicon->entity;

        abort_if($entity === null, 404, 'Favicon owner not found.');

        Gate::authorize('view', $entity);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('filesystems.default'));

        if (! $disk->exists($entityFavicon->path)) {
            abort(404, 'File not found.');
        }

        return $disk->response($entityFavicon->path, null, [
            'Content-Type' => $entityFavicon->mime_type ?: 'image/x-icon',
        ]);
    }
}
