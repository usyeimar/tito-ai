<?php

namespace App\Http\Requests\Central\API\Tenancy;

use App\Http\Requests\Shared\Concerns\HasCanonicalSearchRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexTenantRequest extends FormRequest
{
    use HasCanonicalSearchRules;

    public function rules(): array
    {
        return $this->canonicalSearchRules();
    }
}
