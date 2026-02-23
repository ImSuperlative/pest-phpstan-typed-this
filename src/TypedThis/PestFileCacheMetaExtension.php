<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

use PHPStan\Analyser\ResultCache\ResultCacheMetaExtension;

final readonly class PestFileCacheMetaExtension implements ResultCacheMetaExtension
{
    public function __construct(
        private string $currentWorkingDirectory,
    ) {}

    public function getKey(): string
    {
        return 'pest-phpstan-typed-this-pest-files';
    }

    public function getHash(): string
    {
        $hashes = array_map(
            static fn (string $path) => $path.':'.md5_file($path),
            $this->findPestFiles(),
        );

        sort($hashes);

        return md5(implode("\n", $hashes));
    }

    /** @return list<string> */
    private function findPestFiles(): array
    {
        return $this->findPestFilesIn($this->currentWorkingDirectory);
    }

    /** @return list<string> */
    private function findPestFilesIn(string $directory): array
    {
        $files = $this->collectPestFile($directory);
        foreach ($this->subdirectories($directory) as $subDir) {
            $files = [...$files, ...$this->findPestFilesIn($subDir)];
        }

        return $files;
    }

    /** @return list<string> */
    private function collectPestFile(string $directory): array
    {
        $pestFile = $directory.DIRECTORY_SEPARATOR.'Pest.php';

        return is_file($pestFile) ? [$pestFile] : [];
    }

    /** @return list<string> */
    private function subdirectories(string $directory): array
    {
        $vendorDir = $this->currentWorkingDirectory.DIRECTORY_SEPARATOR.'vendor';

        /** @var list<string> */
        return array_filter(
            glob($directory.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR | GLOB_NOSORT) ?: [],
            static fn (string $subDir) => $subDir !== $vendorDir,
        );
    }
}
