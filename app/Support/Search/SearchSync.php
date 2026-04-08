<?php

namespace App\Support\Search;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SearchSync
{
    /**
     * @param  Model|array<int, Model|null>|null  $models
     */
    public static function afterCommit(Model|array|null $models): void
    {
        $items = Arr::wrap($models);

        $unique = collect($items)
            ->filter(fn (mixed $model): bool => $model instanceof Model)
            ->filter(fn (Model $model): bool => $model->getKey() !== null)
            ->filter(fn (Model $model): bool => method_exists($model, 'searchable'))
            ->unique(fn (Model $model): string => $model::class.':'.$model->getKey())
            ->values();

        if ($unique->isEmpty()) {
            return;
        }

        DB::afterCommit(function () use ($unique): void {
            $unique->each(static function (Model $model): void {
                $model->searchable();
            });
        });
    }
}
