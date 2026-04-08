<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\API\Search;

use Illuminate\Foundation\Http\FormRequest;

class SuggestSearchRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:256'],
        ];
    }
}
