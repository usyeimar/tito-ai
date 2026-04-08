<?php

namespace App\Http\Requests\Central\API\Auth\Tfa;

use Illuminate\Foundation\Http\FormRequest;

class TfaConfirmRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'min:4', 'max:10'],
        ];
    }
}
