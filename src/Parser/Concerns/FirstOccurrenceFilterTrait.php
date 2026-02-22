<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Parser\Concerns;

trait FirstOccurrenceFilterTrait
{
    /**
     * Collect keyed items, keeping only the first occurrence per key and skipping existing keys.
     *
     * @template T
     * @param  iterable<array{string, T}>  $pairs
     * @param  array<string, mixed>  $existingKeys
     * @return array<string, T>
     */
    private function collectFirstOccurrences(iterable $pairs, array $existingKeys = []): array
    {
        $result = [];

        foreach ($pairs as [$key, $value]) {
            if (! isset($existingKeys[$key]) && ! isset($result[$key])) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
