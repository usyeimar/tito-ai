<?php

namespace App\Models\Tenant\Metadata\LicenseType;

use App\Enums\LicenseStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $license_type_id
 * @property string $licenseable_type
 * @property string $licenseable_id
 * @property string|null $license
 * @property Carbon|null $issued_at
 * @property Carbon|null $expires_at
 * @property string|null $issuing_authority
 * @property LicenseStatus|null $status
 * @property string|null $notes
 */
class Licenseable extends MorphPivot
{
    use HasUlids;

    protected $table = 'metadata_licenseables';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'license_type_id',
        'licenseable_type',
        'licenseable_id',
        'license',
        'issued_at',
        'expires_at',
        'issuing_authority',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'status' => LicenseStatus::class,
        ];
    }
}
