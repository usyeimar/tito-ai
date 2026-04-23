<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\API\Agent;

use App\Http\Requests\Shared\Concerns\HasCanonicalSearchRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexAgentToolRequest extends FormRequest
{
    use HasCanonicalSearchRules;

    public function rules(): array
    {
        return [
            ...$this->canonicalSearchRules(),
            'filter.name' => ['nullable', 'string', 'max:255'],
            'filter.is_active' => ['nullable', 'boolean'],
        ];
    }
}
