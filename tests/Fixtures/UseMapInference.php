<?php

use Illuminate\Support\Collection;

beforeEach(function () {
    $this->items = new Collection();
});

it('resolves short class names in inferred types', function () {
    expect($this->items->count())->toBeInt();
});