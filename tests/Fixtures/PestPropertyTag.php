<?php

use Illuminate\Support\Collection;

/**
 * @pest-property Collection $items
 * @pest-property string $name
 */

beforeEach(function () {
    $this->items = new Collection();
    $this->name = 'test';
});

it('reads @pest-property typed properties', function () {
    expect($this->items->count())->toBeInt();
    expect($this->name)->toBeString();
});
