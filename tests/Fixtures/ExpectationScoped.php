<?php

/** @noinspection MultipleExpectChainableInspection */

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Models\Attendee;
use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Models\Form;
use Pest\Expectation;

use function PHPStan\Testing\assertType;

it('types scoped callback parameter (simple)', function () {
    $attendee = new Attendee;

    expect($attendee)->name->scoped(
        function (Expectation $expectation) {
            /** @noinspection PhpExpressionResultUnusedInspection */
            assertType('Pest\Expectation<string>', $expectation);
        },
    );
});

it('types scoped callback without type hint (simple)', function () {
    $attendee = new Attendee;

    expect($attendee)->name->scoped(
        function ($expectation) {
            /** @noinspection PhpExpressionResultUnusedInspection */
            assertType('Pest\Expectation<string>', $expectation);
        },
    );
});

it('types scoped callback on form property', function () {
    $attendee = new Attendee;

    expect($attendee)
        ->form->scoped(function ($form) {
            /** @noinspection PhpExpressionResultUnusedInspection */
            assertType('Pest\Expectation<ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Models\Form>', $form);
        });
});
