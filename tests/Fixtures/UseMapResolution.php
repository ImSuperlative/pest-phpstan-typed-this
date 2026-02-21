<?php

use Illuminate\Support\Collection;

/**
 * @property Collection $items
 */

beforeEach(function () {
    $this->items = new Collection();
});

it('resolves short class names via use map', function () {
    expect($this->items->count())->toBeInt();
});
