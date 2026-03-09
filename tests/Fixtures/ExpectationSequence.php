<?php

/** @noinspection MultipleExpectChainableInspection */

use Illuminate\Support\Collection;
use ImSuperlative\PhpstanPest\Tests\Fixtures\Models\Attendee;
use Pest\Expectation;

use function PHPStan\Testing\assertType;

it('types sequence callback parameters from iterable value type', function () {
    /** @var Collection<int, Attendee> $attendees */
    $attendees = new Collection([new Attendee, new Attendee]);

    // Explicit type hint
    expect($attendees)->sequence(
        function (Expectation $first) {
            $first->id->toBeInt();
            /** @noinspection PhpExpressionResultUnusedInspection */
            assertType('Pest\Expectation<ImSuperlative\PhpstanPest\Tests\Fixtures\Models\Attendee>', $first);
        },
    );

    // No type hint — should infer from iterable value type
    expect($attendees)->sequence(
        function ($first) {
            // first isnt typed either
            $first->id->toBeString();
            /** @noinspection PhpExpressionResultUnusedInspection */
            assertType('Pest\Expectation<ImSuperlative\PhpstanPest\Tests\Fixtures\Models\Attendee>', $first);
        },
    );
});
