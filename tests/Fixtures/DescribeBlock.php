<?php

use function PHPStan\Testing\assertType;

/**
 * @property stdClass $object
 */

beforeEach(function () {
    $this->object = new stdClass();
});

describe('nested group', function () {
    it('has access to typed properties in describe block', function () {
        assertType('stdClass', $this->object);
    });
});
