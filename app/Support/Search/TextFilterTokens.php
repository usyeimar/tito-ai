<?php

namespace App\Support\Search;

class TextFilterTokens
{
    public static function normalized(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    public static function isEmpty(?string $value): bool
    {
        return self::normalized($value) === '';
    }

    /**
     * @return array<int, string>
     */
    public static function prefixes(?string $value): array
    {
        $normalized = self::normalized($value);

        if ($normalized === '') {
            return [];
        }

        $length = mb_strlen($normalized);
        $prefixes = [];

        for ($i = 1; $i <= $length; $i++) {
            $prefixes[] = mb_substr($normalized, 0, $i);
        }

        return array_values(array_unique($prefixes));
    }

    /**
     * @return array<int, string>
     */
    public static function suffixes(?string $value): array
    {
        $normalized = self::normalized($value);

        if ($normalized === '') {
            return [];
        }

        $length = mb_strlen($normalized);
        $suffixes = [];

        for ($i = 0; $i < $length; $i++) {
            $suffixes[] = mb_substr($normalized, $i);
        }

        return array_values(array_unique($suffixes));
    }

    /**
     * @return array<int, string>
     */
    public static function ngrams(?string $value, int $size = 3): array
    {
        $normalized = self::normalized($value);

        if ($normalized === '') {
            return [];
        }

        $length = mb_strlen($normalized);

        if ($length <= $size) {
            return [$normalized];
        }

        $grams = [];

        for ($offset = 0; $offset <= $length - $size; $offset++) {
            $grams[] = mb_substr($normalized, $offset, $size);
        }

        return array_values(array_unique($grams));
    }
}
