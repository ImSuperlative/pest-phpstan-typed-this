<?php

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
