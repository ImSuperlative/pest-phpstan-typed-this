<?php

use Symfony\Component\Process\Process;

/**
 * Run PHPStan analyse on a fixture file and return the result.
 *
 * @return array{exitCode: int, output: string, errors: array<string, mixed>}
 */
function analyseFixture(string $fixture): array
{
    $fixturePath = __DIR__.'/Fixtures/'.$fixture;
    $configPath = __DIR__.'/phpstan-test.neon';
    $phpstanBin = dirname(__DIR__).'/vendor/bin/phpstan';

    $process = new Process([$phpstanBin, 'analyse', '--no-progress', '--error-format=json', '--configuration='.$configPath, $fixturePath]);
    $process->run();

    if ($process->getExitCode() > 1) {
        throw new RuntimeException("PHPStan crashed (exit code {$process->getExitCode()}):\n{$process->getErrorOutput()}");
    }

    $json = json_decode($process->getOutput(), true) ?? [];

    return [
        'exitCode' => (int) $process->getExitCode(),
        'output' => $process->getOutput(),
        'errors' => $json['files'] ?? [],
    ];
}

/**
 * Extract error messages from PHPStan JSON output.
 *
 * @param  array{exitCode: int, output: string, errors: array<string, mixed>}  $result
 * @return list<string>
 */
function getErrorMessages(array $result): array
{
    $messages = [];
    foreach ($result['errors'] as $file) {
        foreach ($file['messages'] as $message) {
            $messages[] = $message['message'];
        }
    }

    return $messages;
}
