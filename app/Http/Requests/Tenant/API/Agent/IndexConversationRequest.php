<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\API\Agent;

use App\Http\Requests\Shared\Concerns\HasCanonicalSearchRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexConversationRequest extends FormRequest
{
    use HasCanonicalSearchRules;

    public function rules(): array
    {
        return [
            ...$this->canonicalSearchRules(),
            'filter.agent_id' => ['nullable', 'string', 'ulid'],
            'filter.status' => ['nullable', 'string', 'in:active,completed,failed,ended'],
            'filter.channel' => ['nullable', 'string', 'max:50'],
            'filter.started_after' => ['nullable', 'date'],
            'filter.started_before' => ['nullable', 'date'],
        ];
    }
}
