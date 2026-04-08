<?php

declare(strict_types=1);

namespace App\Models\Tenant\CRM\Projects;

use App\Models\Concerns\HasImportTracking;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ProjectImport extends Model
{
    use HasImportTracking, HasUlids;

    protected $fillable = [
        'requested_by',
        'idempotency_key',
        'request_hash',
        'mode',
        'status',
        'source_filename',
        'source_path',
        'source_sha256',
        'total_rows',
        'processed_rows',
        'created_count',
        'updated_count',
        'skipped_count',
        'failed_count',
        'warnings_count',
        'warnings_by_code',
        'errors_by_code',
        'report_path',
        'failed_rows_path',
        'error_message',
        'started_at',
        'finished_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return $this->importTrackingCasts();
    }
}
