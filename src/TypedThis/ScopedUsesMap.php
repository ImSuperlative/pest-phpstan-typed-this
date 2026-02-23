<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

/**
 * Immutable map of ->in() scoped class declarations from a Pest.php file.
 *
 * Keys are scope paths (from ->in('path')), null key = unscoped.
 * Values are lists of class-string.
 */
final readonly class ScopedUsesMap
{
    /** @param array<string, list<class-string>> $scopes */
    public function __construct(
        private array $scopes = [],
    ) {}

    /**
     * Get all classes whose ->in() scope matches the given relative path.
     *
     * @return list<class-string>
     */
    public function classesForPath(string $relativePath): array
    {
        /** @var list<class-string> */
        return array_reduce(
            array_keys($this->scopes),
            fn (array $matched, string $scope) => $scope !== '' && $this->pathMatchesScope($relativePath, $scope)
                ? [...$matched, ...$this->scopes[$scope]]
                : $matched,
            [],
        );
    }

    /**
     * Add classes under a scope key.
     *
     * @param  list<class-string>  $classes
     */
    public function withScope(?string $scope, array $classes): self
    {
        $scopes = $this->scopes;
        $key = $scope ?? '';
        $scopes[$key] = [...($scopes[$key] ?? []), ...$classes];

        /** @var array<string, list<class-string>> $scopes */
        return new self($scopes);
    }

    /**
     * Merge unique classes from this map (for a given path) into an existing class list.
     *
     * @param  list<class-string>  $existing
     * @return list<class-string>
     */
    public function mergeInto(array $existing, string $relativePath): array
    {
        return [
            ...$existing,
            ...array_diff($this->classesForPath($relativePath), $existing),
        ];
    }

    private function pathMatchesScope(string $relativePath, string $scope): bool
    {
        return str_starts_with(
            $relativePath,
            str_replace('/', DIRECTORY_SEPARATOR, $scope).DIRECTORY_SEPARATOR
        );
    }
}
