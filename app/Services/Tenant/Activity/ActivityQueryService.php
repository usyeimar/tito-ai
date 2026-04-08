<?php

declare(strict_types=1);

namespace App\Services\Tenant\Activity;

use App\Models\Tenant\Activity\ActivityEvent;
use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\Commons\Addresses\Address;
use App\Models\Tenant\Commons\Emails\Email;
use App\Models\Tenant\Commons\Files\File;
use App\Models\Tenant\Commons\Phones\Phone;
use App\Models\Tenant\CRM\Contacts\ContactAssignment;

use App\Services\Tenant\Activity\Support\KnownMorphTypes;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ActivityQueryService
{
    public function __construct(
//        private readonly SearchRequestNormalizer $normalizer,
//        private readonly SortCompiler $sortCompiler,
//        private readonly SearchContractRepository $contracts,
        private readonly KnownMorphTypes $knownTypes,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return LengthAwarePaginator<ActivityEvent>
     */
    public function paginate(User $viewer, array $input): LengthAwarePaginator
    {
        try {
//            $payload = $this->normalizer->normalize($input);
//            $contract = $this->contracts->forModel(ActivityEvent::class);
//            $sort = $this->sortCompiler->compile($payload['sort'], $contract);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'search' => [$e->getMessage()],
            ]);
        }

        $subjectType = trim((string) data_get($input, 'filter.subject_type', ''));
        $subjectId = trim((string) data_get($input, 'filter.subject_id', ''));
        $includeRelated = data_get($input, 'filter.include_related');
        $includeRelated = is_bool($includeRelated) ? $includeRelated : true;

        $this->assertViewerCanViewSubject($viewer, $subjectType, $subjectId);

        /** @var Builder<ActivityEvent> $query */
        $query = ActivityEvent::query()->with('relations');

        if ($includeRelated) {
            $query->where(static function (Builder $inner) use ($subjectType, $subjectId): void {
                $inner->where('subject_type', $subjectType)
                    ->where('subject_id', $subjectId)
                    ->orWhereHas('relations', static function (Builder $related) use ($subjectType, $subjectId): void {
                        $related->where('related_type', $subjectType)
                            ->where('related_id', $subjectId);
                    });
            });
        } else {
            $query->where('subject_type', $subjectType)->where('subject_id', $subjectId);
        }

//        foreach ($payload['filters'] as $filter) {
//            $field = (string) ($filter['field'] ?? '');
//
//            if (in_array($field, ['subject_type', 'subject_id', 'include_related'], true)) {
//                continue;
//            }
//
//            try {
//                $this->applyFilter($query, $field, (string) ($filter['op'] ?? 'eq'), $filter['value'] ?? null);
//            } catch (InvalidArgumentException $e) {
//                throw ValidationException::withMessages([
//                    'search' => [$e->getMessage()],
//                ]);
//            }
//        }

//        $q = trim($payload['q']);
//        if ($q !== '') {
//            $needle = '%'.strtolower($q).'%';
//
//            $query->where(static function (Builder $inner) use ($needle): void {
//                $inner->whereRaw('LOWER(event_type) LIKE ?', [$needle])
//                    ->orWhereRaw('LOWER(subject_label) LIKE ?', [$needle])
//                    ->orWhereRaw('LOWER(actor_label) LIKE ?', [$needle])
//                    ->orWhereHas('relations', static function (Builder $related) use ($needle): void {
//                        $related->whereRaw('LOWER(related_label) LIKE ?', [$needle]);
//                    });
//            });
//        }
//
//        if ($sort === []) {
//            $sort = ['-occurred_at', '-id'];
//        }

//        foreach ($sort as $token) {
//            $direction = str_starts_with($token, '-') ? 'desc' : 'asc';
//            $field = ltrim($token, '+-');
//
//            $query->orderBy($field, $direction);
//        }
//
//        return $query->paginate(
//            perPage: $payload['per_page'],
//            columns: ['*'],
//            pageName: 'page',
//            page: $payload['page'],
//        );
    }

    /**
     * @param  Builder<ActivityEvent>  $query
     */
    private function applyFilter(Builder $query, string $field, string $op, mixed $value): void
    {
        if ($field === 'occurred_at') {
            $this->applyDateFilter($query, $field, $op, $value);

            return;
        }

        if (! in_array($field, ['event_type', 'actor_id', 'origin', 'request_id', 'workflow_run_id'], true)) {
            throw new InvalidArgumentException("Unsupported filter field [{$field}].");
        }

        $normalizedOp = strtolower($op);

        if ($normalizedOp === 'eq') {
            $query->where($field, '=', (string) $value);

            return;
        }

        if ($normalizedOp === 'ne') {
            $query->where($field, '!=', (string) $value);

            return;
        }

        if (in_array($normalizedOp, ['in', 'not_in', 'nin'], true)) {
            if (! is_array($value) || $value === []) {
                throw new InvalidArgumentException("Filter [{$field}] expects a non-empty list.");
            }

            $list = array_values(array_map(static fn (mixed $item): string => (string) $item, $value));

            if ($normalizedOp === 'in') {
                $query->whereIn($field, $list);
            } else {
                $query->whereNotIn($field, $list);
            }

            return;
        }

        if (in_array($normalizedOp, ['contains', 'starts_with', 'ends_with', 'does_not_contain'], true)) {
            $term = strtolower(trim((string) $value));

            if ($term === '') {
                throw new InvalidArgumentException("Filter [{$field}] expects a non-empty text value.");
            }

            $pattern = match ($normalizedOp) {
                'starts_with' => $term.'%',
                'ends_with' => '%'.$term,
                default => '%'.$term.'%',
            };

            if ($normalizedOp === 'does_not_contain') {
                $query->where(static function (Builder $inner) use ($field, $pattern): void {
                    $inner->whereNull($field)
                        ->orWhereRaw('LOWER('.$field.') NOT LIKE ?', [$pattern]);
                });
            } else {
                $query->whereRaw('LOWER('.$field.') LIKE ?', [$pattern]);
            }

            return;
        }

        throw new InvalidArgumentException("Unsupported filter operator [{$op}] for [{$field}].");
    }

    /**
     * @param  Builder<ActivityEvent>  $query
     */
    private function applyDateFilter(Builder $query, string $field, string $op, mixed $value): void
    {
        $normalizedOp = strtolower($op);

        if ($normalizedOp === 'between') {
            if (! is_array($value) || count($value) !== 2) {
                throw new InvalidArgumentException("Filter [{$field}] expects two values for [between].");
            }

            $from = $this->parseDate($value[0]);
            $to = $this->parseDate($value[1]);
            $query->whereBetween($field, [$from, $to]);

            return;
        }

        $date = $this->parseDate($value);

        $operator = match ($normalizedOp) {
            'eq' => '=',
            'ne' => '!=',
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            default => null,
        };

        if ($operator === null) {
            throw new InvalidArgumentException("Unsupported filter operator [{$op}] for [{$field}].");
        }

        $query->where($field, $operator, $date);
    }

    private function parseDate(mixed $value): CarbonImmutable
    {
        $text = trim((string) $value);

        if ($text === '') {
            throw new InvalidArgumentException('Date filter expects a non-empty value.');
        }

        try {
            return CarbonImmutable::parse($text);
        } catch (\Throwable) {
            throw new InvalidArgumentException("Invalid date value [{$text}].");
        }
    }

    private function assertViewerCanViewSubject(User $viewer, string $subjectType, string $subjectId): void
    {
        $modelClass = $this->knownTypes->modelClassForType($subjectType);

        if ($modelClass === null) {
            throw ValidationException::withMessages([
                'filter.subject_type' => ['Unsupported subject type.'],
            ]);
        }

        /** @var Model $subject */
        $subject = $modelClass::query()->whereKey($subjectId)->firstOrFail();

        if ($subject instanceof Email) {
            $subject->loadMissing('emailable');

            if (! $subject->emailable instanceof Model) {
                abort(404);
            }

            Gate::forUser($viewer)->authorize('view', $subject->emailable);

            return;
        }

        if ($subject instanceof Phone) {
            $subject->loadMissing('phoneable');

            if (! $subject->phoneable instanceof Model) {
                abort(404);
            }

            Gate::forUser($viewer)->authorize('view', $subject->phoneable);

            return;
        }

        if ($subject instanceof Address) {
            $subject->loadMissing('addressable');

            if (! $subject->addressable instanceof Model) {
                abort(404);
            }

            Gate::forUser($viewer)->authorize('view', $subject->addressable);

            return;
        }

        if ($subject instanceof File) {
            $subject->loadMissing('fileable');

            if (! $subject->fileable instanceof Model) {
                abort(404);
            }

            Gate::forUser($viewer)->authorize('view', $subject->fileable);

            return;
        }

        if ($subject instanceof Assignment) {
            $subject->loadMissing('assignable');

            if (! $subject->assignable instanceof Model) {
                abort(404);
            }

            Gate::forUser($viewer)->authorize('view', $subject->assignable);

            return;
        }

        if ($subject instanceof ContactAssignment) {
            $subject->loadMissing('assignable');

            if (! $subject->assignable instanceof Model) {
                abort(404);
            }

            Gate::forUser($viewer)->authorize('view', $subject->assignable);

            return;
        }

        Gate::forUser($viewer)->authorize('view', $subject);
    }
}
