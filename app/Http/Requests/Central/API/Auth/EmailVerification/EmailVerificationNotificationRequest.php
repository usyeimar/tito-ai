<?php

namespace App\Http\Requests\Central\API\Auth\EmailVerification;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationNotificationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['sometimes', 'string', 'in:signup,update'],
        ];
    }
}
