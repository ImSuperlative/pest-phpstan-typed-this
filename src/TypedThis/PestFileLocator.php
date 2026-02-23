<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

/**
 * Locates parent Pest.php files for a given test file path.
 *
 * Handles PHPStorm's temp directory analysis by resolving
 * temp paths back to the real project path.
 */
final readonly class PestFileLocator
{
    public function __construct(
        private string $currentWorkingDirectory,
    ) {}

    /** @return list<string> */
    public function findParentPestFiles(string $filePath): array
    {
        return array_values(array_filter(
            $this->parentPestPaths($filePath),
            fn (string $path) => is_file($path),
        ));
    }

    /**
     * Resolve a potentially temp IDE path back to the real project path.
     *
     * PHPStorm copies files to a temp directory for analysis, preserving
     * the relative structure. We detect this and map back to the real path.
     */
    public function resolveRealPath(string $filePath): string
    {
        if (str_starts_with($filePath, $this->currentWorkingDirectory)) {
            return $filePath;
        }

        $relativeSuffix = $this->extractRelativeSuffix($filePath);

        return $relativeSuffix !== null
            ? $this->currentWorkingDirectory.DIRECTORY_SEPARATOR.$relativeSuffix
            : $filePath;
    }

    /** @return list<string> */
    private function parentPestPaths(string $filePath): array
    {
        $realPath = $this->resolveRealPath($filePath);
        $candidates = [];
        $dir = dirname($realPath, 2);

        while (($parent = dirname($dir)) !== $dir) {
            $candidates[] = $dir.DIRECTORY_SEPARATOR.'Pest.php';
            $dir = $parent;
        }

        $candidates[] = $dir.DIRECTORY_SEPARATOR.'Pest.php';

        return $candidates;
    }

    private function extractRelativeSuffix(string $filePath): ?string
    {
        $parts = explode(DIRECTORY_SEPARATOR, $filePath);

        for ($i = 1, $count = count($parts); $i < $count; $i++) {
            $suffix = implode(DIRECTORY_SEPARATOR, array_slice($parts, $i));
            $candidate = $this->currentWorkingDirectory.DIRECTORY_SEPARATOR.$suffix;

            if (is_file($candidate)) {
                return $suffix;
            }
        }

        return null;
    }
}
