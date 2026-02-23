<?php

namespace ImSuperlative\PestPhpstanTypedThis\Tests;

use RuntimeException;
use Symfony\Component\Process\Process;

trait AnalysesFixtures
{
    /** @var array<string, array<string, array{messages: list<array{message: string, line: int}>}>> */
    private static array $phpstanCache = [];

    /**
     * Analyse a fixture using the default test config (batched with all fixtures).
     *
     * @return array{exitCode: int, messages: list<string>}
     */
    protected function analyseFixture(string $fixture, string $config = 'phpstan-test.neon'): array
    {
        $cacheKey = $config === 'phpstan-test.neon' ? $config : $config.':'.$fixture;

        if (! isset(self::$phpstanCache[$cacheKey])) {
            self::$phpstanCache[$cacheKey] = $config === 'phpstan-test.neon'
                ? $this->runAllFixtures($config)
                : $this->runSingleFixture($fixture, $config);
        }

        $data = self::$phpstanCache[$cacheKey][$fixture] ?? ['messages' => []];
        $messages = array_map(fn (array $m) => $m['message'], $data['messages']);

        return [
            'exitCode' => $messages === [] ? 0 : 1,
            'messages' => $messages,
        ];
    }

    /**
     * @return array<string, array{messages: list<array{message: string, line: int}>}>
     */
    private function runAllFixtures(string $config): array
    {
        $fixtureDir = __DIR__.'/Fixtures';
        $fixtures = glob($fixtureDir.'/*.php') ?: [];

        return $this->runPhpstan($fixtures, __DIR__.'/'.$config);
    }

    /**
     * @return array<string, array{messages: list<array{message: string, line: int}>}>
     */
    private function runSingleFixture(string $fixture, string $config): array
    {
        return $this->runPhpstan(
            [__DIR__.'/Fixtures/'.$fixture],
            __DIR__.'/'.$config,
        );
    }

    /**
     * @param  list<string>  $paths
     * @return array<string, array{messages: list<array{message: string, line: int}>}>
     */
    private function runPhpstan(array $paths, string $config): array
    {
        $process = new Process([
            dirname(__DIR__).'/vendor/bin/phpstan',
            'analyse',
            '--no-progress',
            '--error-format=json',
            '--configuration='.$config,
            ...$paths,
        ]);
        $process->run();

        if ($process->getExitCode() > 1) {
            throw new RuntimeException(sprintf(
                'PHPStan crashed (exit code %d):\n%s',
                $process->getExitCode(),
                $process->getErrorOutput(),
            ));
        }

        $json = json_decode($process->getOutput(), true) ?? [];

        $results = [];
        foreach ($json['files'] ?? [] as $path => $data) {
            $results[basename($path)] = $data;
        }

        return $results;
    }
}
