<?php

/** @noinspection StaticClosureCanBeUsedInspection */

use ImSuperlative\PestPhpstanTypedThis\Tests\AnalysesFixtures;
use ImSuperlative\PestPhpstanTypedThis\Tests\TypeInferenceTestCase;

uses(AnalysesFixtures::class);

$fixtureDir = __DIR__.'/../Fixtures';
$configFile = __DIR__.'/../phpstan-test.neon';

/**
 * Run type inference assertions for a fixture file.
 *
 * @param  list<string>  $configFiles
 */
function assertTypeInference(string $fixtureFile, array $configFiles): void
{
    TypeInferenceTestCase::setConfigFiles($configFiles);

    /** @var TypeInferenceTestCase $testCase */
    $testCase = test();

    foreach (TypeInferenceTestCase::assertTypesForFile($fixtureFile) as $assert) {
        $assertType = array_shift($assert);
        $file = array_shift($assert);
        $testCase->assertFileAsserts($assertType, $file, ...$assert);
    }
}

it('passes with @property annotations', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/PropertyAnnotation.php", [$configFile]);
});

it('infers types from new expressions', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/NewInference.php", [$configFile]);
});

it('resolves short class names via use map', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/UseMapResolution.php", [$configFile]);
});

it('gives annotation precedence over inferred types', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/AnnotationPrecedence.php", [$configFile]);
});

it('allows writing to typed properties', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/WritableProperty.php", [$configFile]);
});

it('works without any dynamic properties', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/NoProperties.php", [$configFile]);
});

it('types $this in test() function', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/TestFunction.php", [$configFile]);
});

it('types $this inside describe blocks', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/DescribeBlock.php", [$configFile]);
});

it('types $this in all hook functions', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/HookFunctions.php", [$configFile]);
});

it('infers scalar types from assignments', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/ScalarInference.php", [$configFile]);
});

it('handles multiple properties with different types', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/MultipleProperties.php", [$configFile]);
});

it('resolves use imports in inferred types', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/UseMapInference.php", [$configFile]);
});

it('parses @pest-property custom tags', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/PestPropertyTag.php", [$configFile]);
});

it('gives @pest-property precedence over @property', function () use ($fixtureDir, $configFile) {
    assertTypeInference("$fixtureDir/PestPropertyPrecedence.php", [$configFile]);
});

it('allows dynamic methods on Pest Expectation', function () {
    expect($this->analyseFixture('ExpectationExtension.php')['exitCode'])
        ->toBe(0);
});

it('allows higher-order property access on expectations', function () {
    expect($this->analyseFixture('ExpectationPropertyAccess.php')['exitCode'])
        ->toBe(0);
});

describe('uses() trait resolution', function () use ($fixtureDir, $configFile) {
    it('resolves trait methods from uses()', function () use ($fixtureDir, $configFile) {
        assertTypeInference("$fixtureDir/UsesResolution/UsesFunction.php", [$configFile]);
    });

    it('resolves trait methods from pest()->extend()', function () use ($fixtureDir, $configFile) {
        assertTypeInference("$fixtureDir/UsesResolution/PestExtend.php", [$configFile]);
    });

    it('resolves trait methods from pest()->extends()', function () use ($fixtureDir, $configFile) {
        assertTypeInference("$fixtureDir/UsesResolution/PestExtends.php", [$configFile]);
    });

    it('resolves trait methods from pest()->use()', function () use ($fixtureDir, $configFile) {
        assertTypeInference("$fixtureDir/UsesResolution/PestUse.php", [$configFile]);
    });

    it('resolves trait methods from pest()->uses()', function () use ($fixtureDir, $configFile) {
        assertTypeInference("$fixtureDir/UsesResolution/PestUses.php", [$configFile]);
    });

    it('resolves trait methods from pest()->group()->extend()', function () use ($fixtureDir, $configFile) {
        assertTypeInference("$fixtureDir/UsesResolution/PestGroupExtend.php", [$configFile]);
    });
});

// --- Error-expecting tests (subprocess-based) ---

it('reports errors for undefined properties', function () {
    $result = $this->analyseFixture('UndefinedProperty.php');

    expect($result['exitCode'])->toBe(1)
        ->and($result['messages'][0])->toContain('undefined property');
});

it('reports errors for type mismatches on typed properties', function () {
    expect($this->analyseFixture('TypeMismatch.php')['exitCode'])
        ->toBe(1);
});

it('ignores @property when parsePhpDocProperties is disabled', function () {
    expect($this->analyseFixture('DisabledPhpDocParser.php', 'phpstan-test-defaults.neon')['exitCode'])
        ->toBe(0);
});

it('applies @property when parsePhpDocProperties is enabled', function () {
    expect($this->analyseFixture('DisabledPhpDocParser.php')['exitCode'])
        ->toBe(1);
});

it('falls back gracefully when expectationPropertyAccess is disabled', function () {
    expect($this->analyseFixture('ExpectationPropertyAccess.php', 'phpstan-test-no-property-access.neon')['exitCode'])
        ->toBe(1);
});
