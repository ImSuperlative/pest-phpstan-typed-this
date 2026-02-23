<?php

use Illuminate\Support\Collection;
use function PHPStan\Testing\assertType;

/**
 * @pest-property Collection $items
 * @pest-property string $name
 */

beforeEach(function () {
    $this->items = new Collection();
    $this->name = 'test';
});

it('reads @pest-property typed properties', function () {
    assertType('Illuminate\Support\Collection', $this->items);
    assertType('string', $this->name);
});
