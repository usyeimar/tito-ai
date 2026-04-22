<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Central\System\SystemProfilePicture as CentralSystemProfilePicture;
use App\Models\Tenant\System\SystemProfilePicture as TenantSystemProfilePicture;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ProfilePictureController extends Controller
{
    public function show(Request $request, string $profilePicture): Response
    {
        $user = $request->user();

        if (! $user || ! $user->global_id) {
            abort(403, 'Access denied.');
        }

        $modelClass = tenant() ? TenantSystemProfilePicture::class : CentralSystemProfilePicture::class;

        /** @var CentralSystemProfilePicture|TenantSystemProfilePicture $picture */
        $picture = $modelClass::query()->whereKey($profilePicture)->firstOrFail();

        if ($picture->user_global_id !== $user->global_id) {
            abort(403, 'Access denied.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('filesystems.default'));

        if (! $disk->exists($picture->path)) {
            abort(404, 'File not found.');
        }

        return $disk->response($picture->path, null, [
            'Content-Type' => 'image/webp',
        ]);
    }
}
