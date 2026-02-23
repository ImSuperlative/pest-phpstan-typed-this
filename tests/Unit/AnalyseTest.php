<?php

/** @noinspection StaticClosureCanBeUsedInspection */

use Symfony\Component\Process\Process;

/**
 * @return array{exitCode: int, output: string, errors: array<string, mixed>}
 */
function analyseFixture(string $fixture, string $config = 'phpstan-test.neon'): array
{
    $fixturePath = dirname(__DIR__).'/Fixtures/'.$fixture;
    $configPath = dirname(__DIR__).'/'.$config;
    $phpstanBin = dirname(__DIR__, 2).'/vendor/bin/phpstan';

    $process = new Process([
        $phpstanBin,
        'analyse',
        '--no-progress',
        '--error-format=json',
        '--configuration='.$configPath,
        $fixturePath,
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
 * @return array<string>
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

it('passes with @property annotations', function () {
    $result = analyseFixture('PropertyAnnotation.php');

    expect($result['exitCode'])->toBe(0);
});

it('infers types from new expressions', function () {
    $result = analyseFixture('NewInference.php');

    expect($result['exitCode'])->toBe(0);
});

it('resolves short class names via use map', function () {
    $result = analyseFixture('UseMapResolution.php');

    expect($result['exitCode'])->toBe(0);
});

it('gives annotation precedence over inferred types', function () {
    $result = analyseFixture('AnnotationPrecedence.php');

    expect($result['exitCode'])->toBe(0);
});

it('allows writing to typed properties', function () {
    $result = analyseFixture('WritableProperty.php');

    expect($result['exitCode'])->toBe(0);
});

it('works without any dynamic properties', function () {
    $result = analyseFixture('NoProperties.php');

    expect($result['exitCode'])->toBe(0);
});

it('reports errors for undefined properties', function () {
    $result = analyseFixture('UndefinedProperty.php');
    $messages = getErrorMessages($result);

    expect($result['exitCode'])->toBe(1)
        ->and($messages[0])->toContain('undefined property');
});

it('types $this in test() function', function () {
    $result = analyseFixture('TestFunction.php');

    expect($result['exitCode'])->toBe(0);
});

it('types $this inside describe blocks', function () {
    $result = analyseFixture('DescribeBlock.php');

    expect($result['exitCode'])->toBe(0);
});

it('types $this in all hook functions', function () {
    $result = analyseFixture('HookFunctions.php');

    expect($result['exitCode'])->toBe(0);
});

it('reports errors for type mismatches on typed properties', function () {
    $result = analyseFixture('TypeMismatch.php');

    expect($result['exitCode'])->toBe(1);
});

it('infers scalar types from assignments', function () {
    $result = analyseFixture('ScalarInference.php');

    expect($result['exitCode'])->toBe(0);
});

it('handles multiple properties with different types', function () {
    $result = analyseFixture('MultipleProperties.php');

    expect($result['exitCode'])->toBe(0);
});

it('resolves use imports in inferred types', function () {
    $result = analyseFixture('UseMapInference.php');

    expect($result['exitCode'])->toBe(0);
});

it('parses @pest-property custom tags', function () {
    $result = analyseFixture('PestPropertyTag.php');

    expect($result['exitCode'])->toBe(0);
});

it('gives @pest-property precedence over @property', function () {
    $result = analyseFixture('PestPropertyPrecedence.php');

    expect($result['exitCode'])->toBe(0);
});

it('ignores @property when parsePhpDocProperties is disabled', function () {
    $result = analyseFixture('DisabledPhpDocParser.php', 'phpstan-test-defaults.neon');

    expect($result['exitCode'])->toBe(0);
});

it('applies @property when parsePhpDocProperties is enabled', function () {
    $result = analyseFixture('DisabledPhpDocParser.php');

    expect($result['exitCode'])->toBe(1);
});

it('allows dynamic methods on Pest Expectation', function () {
    $result = analyseFixture('ExpectationExtension.php');

    expect($result['exitCode'])->toBe(0);
});

it('allows higher-order property access on expectations', function () {
    $result = analyseFixture('ExpectationPropertyAccess.php');

    expect($result['exitCode'])->toBe(0);
});

it('falls back gracefully when expectationPropertyAccess is disabled', function () {
    $result = analyseFixture('ExpectationPropertyAccess.php', 'phpstan-test-no-property-access.neon');

    expect($result['exitCode'])->toBe(1);
});

