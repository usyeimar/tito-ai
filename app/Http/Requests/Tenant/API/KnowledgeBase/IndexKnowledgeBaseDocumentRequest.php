<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\API\KnowledgeBase;

use App\Http\Requests\Shared\Concerns\HasCanonicalSearchRules;
use Illuminate\Foundation\Http\FormRequest;

class IndexKnowledgeBaseDocumentRequest extends FormRequest
{
    use HasCanonicalSearchRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->canonicalSearchRules();
    }
}
