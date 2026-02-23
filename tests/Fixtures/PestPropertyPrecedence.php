<?php

use Illuminate\Support\Collection;
use function PHPStan\Testing\assertType;

/**
 * pest-property should win over property.
 * pest-property declares Collection, property declares ?Collection (nullable).
 * If pest-property wins, type is Collection (non-null).
 * If property wins, type would be ?Collection (nullable).
 *
 * @pest-property Collection $thing
 * @property ?Collection $thing
 */

beforeEach(function () {
    $this->thing = new Collection();
});

it('gives @pest-property precedence over @property', function () {
    assertType('Illuminate\Support\Collection', $this->thing);
});
