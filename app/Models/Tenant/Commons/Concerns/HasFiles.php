<?php

namespace App\Models\Tenant\Commons\Concerns;

use App\Models\Tenant\Commons\Files\File;
use App\Models\Tenant\Commons\Files\FileFolder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFiles
{
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function fileFolders(): MorphMany
    {
        return $this->morphMany(FileFolder::class, 'fileable');
    }
}
