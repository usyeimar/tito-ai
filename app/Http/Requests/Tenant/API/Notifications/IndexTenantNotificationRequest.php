<?php

namespace App\Http\Requests\Tenant\API\Notifications;

use Illuminate\Foundation\Http\FormRequest;

class IndexTenantNotificationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'array'],
            'page.number' => ['nullable', 'integer', 'min:1'],
            'page.size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'filter' => ['nullable', 'array'],
            'filter.unread' => ['nullable', 'boolean'],
        ];
    }
}
