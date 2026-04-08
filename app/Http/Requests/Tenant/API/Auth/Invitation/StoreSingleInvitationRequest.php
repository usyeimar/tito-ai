<?php

namespace App\Http\Requests\Tenant\API\Auth\Invitation;

use Illuminate\Foundation\Http\FormRequest;

class StoreSingleInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
