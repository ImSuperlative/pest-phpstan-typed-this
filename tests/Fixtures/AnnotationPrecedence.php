<?php

use Illuminate\Support\Collection;

/**
 * Annotation declares $thing as Collection, assignment infers stdClass.
 * The annotation should win, so calling count() (a Collection method) should pass.
 *
 * @property Collection $thing
 */

beforeEach(function () {
    $this->thing = new Collection();
});

it('uses annotation type over inferred type', function () {
    // This works because annotation says Collection, which has count().
    expect($this->thing->count())->toBeInt();
});
