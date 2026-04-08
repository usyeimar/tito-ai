<?php

namespace App\Models\Tenant\Metadata\CustomField;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalCustomFieldDefinition extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'global_custom_field_definitions';

    protected $fillable = [
        'entity_type',
        'name',
        'label',
        'type',
        'options',
        'is_required',
        'default_value',
        'position',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'position' => 'integer',
    ];
}
