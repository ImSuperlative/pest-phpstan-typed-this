<?php

use Illuminate\Support\Collection;
use function PHPStan\Testing\assertType;

/**
 * @property Collection $items
 */

beforeEach(function () {
    $this->items = new Collection();
});

it('resolves short class names via use map', function () {
    assertType('Illuminate\Support\Collection', $this->items);
});
