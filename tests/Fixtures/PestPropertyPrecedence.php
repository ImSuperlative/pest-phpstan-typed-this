<?php

use Illuminate\Support\Collection;

/**
 * pest-property should win over property.
 * pest-property declares Collection, property declares ?Collection (nullable).
 * If pest-property wins, count() can be called directly (non-null).
 * If property wins, count() would require a null check.
 *
 * @pest-property Collection $thing
 * @property ?Collection $thing
 */

beforeEach(function () {
    $this->thing = new Collection();
});

it('gives @pest-property precedence over @property', function () {
    expect($this->thing->count())->toBeInt();
});
