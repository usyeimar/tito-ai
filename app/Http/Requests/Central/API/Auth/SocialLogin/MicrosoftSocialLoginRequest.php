<?php

namespace App\Http\Requests\Central\API\Auth\SocialLogin;

use Illuminate\Foundation\Http\FormRequest;

class MicrosoftSocialLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'access_token' => ['required', 'string'],
            'device_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
