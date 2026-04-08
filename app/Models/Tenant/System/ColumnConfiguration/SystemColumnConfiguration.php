<?php

namespace App\Models\Tenant\System\ColumnConfiguration;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemColumnConfiguration extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'system_column_configurations';

    protected $fillable = [
        'module',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
