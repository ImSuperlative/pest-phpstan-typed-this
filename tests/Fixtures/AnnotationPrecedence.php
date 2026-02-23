<?php

use Illuminate\Support\Collection;
use function PHPStan\Testing\assertType;

/**
 * Annotation declares $thing as ?Collection (nullable), assignment infers Collection (non-null).
 * If annotation wins, $thing is ?Collection so calling count() directly would be an error.
 *
 * @property ?Collection $thing
 */

beforeEach(function () {
    $this->thing = new Collection();
});

it('uses annotation type over inferred type', function () {
    assertType('Illuminate\Support\Collection|null', $this->thing);
});
