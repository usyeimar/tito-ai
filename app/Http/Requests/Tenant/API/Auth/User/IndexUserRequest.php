<?php

namespace App\Http\Requests\Tenant\API\Auth\User;

use App\Http\Requests\Shared\Concerns\HasCanonicalSearchRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexUserRequest extends FormRequest
{
    use HasCanonicalSearchRules;

    public function rules(): array
    {
        return $this->canonicalSearchRules();
    }
}
