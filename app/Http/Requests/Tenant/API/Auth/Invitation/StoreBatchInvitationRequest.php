<?php

namespace App\Http\Requests\Tenant\API\Auth\Invitation;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emails' => ['required', 'array', 'max:50'],
            'emails.*' => ['required', 'email', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'emails.max' => 'Batch invitations are limited to 50 emails at a time.',
        ];
    }
}
