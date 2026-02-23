<?php

use Illuminate\Support\Collection;
use function PHPStan\Testing\assertType;

/**
 * @property string $name
 * @property int $age
 * @property Collection $items
 */

beforeEach(function () {
    $this->name = 'John';
    $this->age = 30;
    $this->items = new Collection();
});

it('handles multiple properties with different types', function () {
    assertType('string', $this->name);
    assertType('int', $this->age);
    assertType('Illuminate\Support\Collection', $this->items);
});
