<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

/**
 * Validates that a date field is after or equal to a sibling field
 * within the same array item. Works correctly with wildcard paths
 * like `items.*.expires_at` compared to `items.*.issued_at`.
 */
class AfterOrEqualSibling implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    public function __construct(
        protected readonly string $siblingField,
    ) {}

    /** @param array<string, mixed> $data */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $segments = explode('.', $attribute);
        $segments[count($segments) - 1] = $this->siblingField;
        $siblingPath = implode('.', $segments);

        $siblingValue = data_get($this->data, $siblingPath);

        if ($siblingValue === null || $siblingValue === '') {
            return;
        }

        if (Carbon::parse($value)->lt(Carbon::parse($siblingValue))) {
            $fail("The :attribute must be a date after or equal to {$this->siblingField}.");
        }
    }
}
