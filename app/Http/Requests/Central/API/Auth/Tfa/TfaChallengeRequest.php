<?php

namespace App\Http\Requests\Central\API\Auth\Tfa;

use Illuminate\Foundation\Http\FormRequest;

class TfaChallengeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }
}
