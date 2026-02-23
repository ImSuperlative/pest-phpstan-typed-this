<?php

use function PHPStan\Testing\assertType;

/**
 * @property stdClass $object
 * @property ?stdClass $nullableObject
 */

beforeEach(function () {
    $this->object = new stdClass();
    $this->nullableObject = null;
});

it('reads typed property from annotation', function () {
    assertType('stdClass', $this->object);
});

it('reads nullable property from annotation', function () {
    assertType('stdClass|null', $this->nullableObject);
});
