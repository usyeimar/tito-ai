<?php

namespace App\Http\Requests\Central\API\Auth\Impersonation;

use Illuminate\Foundation\Http\FormRequest;

class CreateTenantImpersonationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenant' => ['required', 'string', 'max:255'],
            'redirect_url' => ['sometimes', 'string', 'max:2048', 'starts_with:/'],
        ];
    }
}
