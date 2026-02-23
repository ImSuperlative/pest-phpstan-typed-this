<?php

use Illuminate\Support\Collection;
use function PHPStan\Testing\assertType;

beforeEach(function () {
    $this->items = new Collection;
});

it('resolves short class names in inferred types', function () {
    assertType('Illuminate\Support\Collection', $this->items);
});
