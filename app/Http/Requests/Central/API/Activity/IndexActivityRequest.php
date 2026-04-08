<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\API\Activity;

use App\Http\Requests\Shared\Concerns\HasCanonicalSearchRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class IndexActivityRequest extends FormRequest
{
    use HasCanonicalSearchRules;

    public function rules(): array
    {
        $knownTypes = array_keys((array) config('activity-log.types', []));

        return array_merge(
            $this->canonicalSearchRules(200),
            [
                'filter.subject_type' => ['required', 'string', Rule::in($knownTypes)],
                'filter.subject_id' => ['required', 'string', 'max:64'],
                'filter.include_related' => ['nullable', 'boolean'],
                'filter.event_type' => ['nullable', 'string', 'max:200'],
                'filter.actor_id' => ['nullable', 'string', 'max:64'],
                'filter.origin' => ['nullable', 'string', 'max:32'],
                'filter.request_id' => ['nullable', 'string', 'max:128'],
                'filter.workflow_run_id' => ['nullable', 'string', 'max:64'],
                'filter.occurred_at' => ['nullable', 'string', 'max:128'],
                'include' => ['prohibited'],
            ],
        );
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = [
                'subject_type',
                'subject_id',
                'include_related',
                'event_type',
                'actor_id',
                'origin',
                'request_id',
                'workflow_run_id',
                'occurred_at',
            ];

            $submitted = array_keys((array) $this->input('filter', []));
            $unknown = array_values(array_diff($submitted, $allowed));

            if ($unknown !== []) {
                $validator->errors()->add('filter', 'Unsupported filter keys: '.implode(', ', $unknown));
            }
        });
    }
}
