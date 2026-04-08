<?php

namespace App\Models\Central\Auth\Role;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasUlids;

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getKey(),
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'search_blob' => trim(implode(' ', array_filter([
                $this->name,
                $this->guard_name,
            ]))),
        ];
    }
}
