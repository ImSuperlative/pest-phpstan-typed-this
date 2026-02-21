<?php

beforeEach(function () {
    $this->name = 'hello';
    $this->count = 42;
    $this->items = [1, 2, 3];
});

it('infers scalar types from assignments', function () {
    expect($this->name)->toBeString()
        ->and($this->count)->toBeInt()
        ->and($this->items)->toBeArray();
});
