<?php

namespace App\Http\Requests\Central\API\Auth\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilePictureRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'profile_picture' => ['required', 'file', 'image', 'max:5120'],
        ];
    }
}
