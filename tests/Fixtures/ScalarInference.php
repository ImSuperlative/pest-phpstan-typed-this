<?php

use function PHPStan\Testing\assertType;

beforeEach(function () {
    $this->name = 'hello';
    $this->count = 42;
    $this->items = [1, 2, 3];
});

it('infers scalar types from assignments', function () {
    assertType("'hello'", $this->name);
    assertType('42', $this->count);
    assertType('array{1, 2, 3}', $this->items);
});
