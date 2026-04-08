<?php

declare(strict_types=1);

namespace App\Services\Tenant\Activity\Support;

use App\Services\Tenant\Activity\DTOs\ActivityContext;

class ActivityContextStore
{
    /**
     * @var array<int, ActivityContext>
     */
    private static array $stack = [];

    public static function current(): ?ActivityContext
    {
        if (self::$stack === []) {
            return null;
        }

        return self::$stack[array_key_last(self::$stack)] ?? null;
    }

    public static function push(ActivityContext $context): void
    {
        self::$stack[] = $context;
    }

    public static function pop(): void
    {
        array_pop(self::$stack);
    }

    /**
     * @template T
     *
     * @param  callable():T  $callback
     * @return T
     */
    public static function runWith(ActivityContext $context, callable $callback): mixed
    {
        self::push($context);

        try {
            return $callback();
        } finally {
            self::pop();
        }
    }
}
