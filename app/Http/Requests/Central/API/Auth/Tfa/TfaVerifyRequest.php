<?php

namespace App\Http\Requests\Central\API\Auth\Tfa;

use Illuminate\Foundation\Http\FormRequest;

class TfaVerifyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tfa_token' => ['required', 'string'],
            'code' => ['required', 'string', 'min:4', 'max:64'],
            'device_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
