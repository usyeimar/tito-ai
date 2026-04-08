<?php

namespace App\Http\Requests\Tenant\API\Notifications;

use Illuminate\Foundation\Http\FormRequest;

class BatchNotificationIdsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['required', 'string', 'distinct'],
        ];
    }
}
