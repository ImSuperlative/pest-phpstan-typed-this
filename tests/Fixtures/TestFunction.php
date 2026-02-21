<?php

/**
 * @property stdClass $object
 */

beforeEach(function () {
    $this->object = new stdClass();
});

test('typed property works in test() function', function () {
    expect($this->object)->toBeObject();
});
