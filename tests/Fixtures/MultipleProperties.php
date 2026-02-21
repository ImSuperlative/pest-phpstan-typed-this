<?php

use Illuminate\Support\Collection;

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
    expect($this->name)->toBeString()
        ->and($this->age)->toBeInt()
        ->and($this->items->count())->toBeInt();
});
