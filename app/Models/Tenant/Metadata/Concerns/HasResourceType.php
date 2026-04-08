<?php

namespace App\Models\Tenant\Metadata\Concerns;

use App\Enums\ModuleType;
use App\Models\Tenant\Metadata\ResourceType\ResourceType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasResourceType
{
    abstract public function getResourceTypeModuleType(): ModuleType;

    public function type(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class, 'type_id')
            ->where('module_type', $this->getResourceTypeModuleType());
    }
}
