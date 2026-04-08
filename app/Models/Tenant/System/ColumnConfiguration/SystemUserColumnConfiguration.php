<?php

namespace App\Models\Tenant\System\ColumnConfiguration;

use App\Models\Tenant\Auth\Authentication\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemUserColumnConfiguration extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'system_user_column_configurations';

    protected $fillable = [
        'user_id',
        'module',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
