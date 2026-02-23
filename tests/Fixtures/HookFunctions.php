<?php

use function PHPStan\Testing\assertType;

/**
 * @property stdClass $object
 */

beforeEach(function () {
    $this->object = new stdClass();
});

afterEach(function () {
    assertType('stdClass', $this->object);
});

it('has typed properties across hooks', function () {
    assertType('stdClass', $this->object);
});
