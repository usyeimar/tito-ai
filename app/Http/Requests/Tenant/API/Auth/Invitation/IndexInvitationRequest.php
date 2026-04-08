<?php

namespace App\Http\Requests\Tenant\API\Auth\Invitation;

use Illuminate\Foundation\Http\FormRequest;

class IndexInvitationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'array'],
            'filter.search' => ['nullable', 'string', 'max:255'],
            'q' => ['prohibited'],
            'sort' => ['prohibited'],
            'include' => ['prohibited'],
            'page' => ['prohibited'],
            'per_page' => ['prohibited'],
        ];
    }
}
