<?php

namespace App\Http\Requests\Tenant\API\Auth\User;

use App\Enums\ModuleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            // We check email uniqueness on the tenant level here and then central uniqueness on the controller level
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'type_id' => [
                'sometimes',
                'nullable',
                'string',
                Rule::exists('metadata_types', 'id')->where('module_type', ModuleType::USERS->value),
            ],
        ];
    }
}
