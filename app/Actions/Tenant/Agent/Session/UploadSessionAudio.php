<?php

namespace App\Actions\Tenant\Agent\Session;

use App\Models\Tenant\Agent\AgentSession;
use App\Models\Tenant\Commons\File;
use Illuminate\Http\UploadedFile;

class UploadSessionAudio
{
    public function __invoke(AgentSession $session, UploadedFile $file): File
    {
        $path = $file->store($session->agent->id.'/sessions/'.$session->id, 'local');

        return $session->files()->create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'local',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}
