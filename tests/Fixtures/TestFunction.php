<?php

use function PHPStan\Testing\assertType;

/**
 * @property stdClass $object
 */

beforeEach(function () {
    $this->object = new stdClass();
});

test('typed property works in test() function', function () {
    assertType('stdClass', $this->object);
});
