<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\API\Agent;

use App\Http\Requests\Shared\Concerns\HasCanonicalSearchRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexTrunkRequest extends FormRequest
{
    use HasCanonicalSearchRules;

    public function rules(): array
    {
        return [
            ...$this->canonicalSearchRules(),
            'filter.status' => ['nullable', 'string', 'in:active,inactive,suspended'],
            'filter.mode' => ['nullable', 'string', 'in:inbound,register,outbound'],
            'filter.agent_id' => ['nullable', 'string', 'ulid'],
            'filter.name' => ['nullable', 'string', 'max:255'],
            'filter.search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
