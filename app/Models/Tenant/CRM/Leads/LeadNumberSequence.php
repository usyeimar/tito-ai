<?php

declare(strict_types=1);

namespace App\Models\Tenant\CRM\Leads;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadNumberSequence extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'key',
        'format',
        'current_value',
    ];

    protected function casts(): array
    {
        return [
            'current_value' => 'integer',
        ];
    }
}
