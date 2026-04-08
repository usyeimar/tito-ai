<?php

namespace App\Http\Requests\Tenant\API\Auth\Role;

use App\Http\Requests\Shared\Concerns\HasCanonicalSearchRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexRoleRequest extends FormRequest
{
    use HasCanonicalSearchRules;

    public function rules(): array
    {
        return $this->canonicalSearchRules();
    }
}
