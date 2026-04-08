<?php

namespace App\Models\Tenant\System\Configuration;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'system_configurations';

    protected $fillable = [
        'key',
        'data',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'encrypted:array',
            'meta' => 'encrypted:array',
        ];
    }

    public function isActive(): bool
    {
        return (bool) ($this->normalizedMeta()['is_active'] ?? false);
    }

    public function normalizedMeta(): ?array
    {
        if (! in_array($this->key, ['aws_ses', 'allowed_email_addresses', 'documenso'], true)) {
            return $this->meta;
        }

        $lastValidationError = $this->meta['last_validation_error'] ?? null;

        if (is_string($lastValidationError)) {
            $lastValidationError = [
                'code' => 'validation_failed',
                'message' => $lastValidationError,
                'failed_checks' => [],
            ];
        }

        return [
            'is_active' => (bool) ($this->meta['is_active'] ?? false),
            'last_validated_at' => $this->meta['last_validated_at'] ?? null,
            'last_validation_error' => $lastValidationError,
        ];
    }
}
