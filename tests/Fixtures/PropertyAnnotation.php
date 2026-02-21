<?php

/**
 * @property stdClass $object
 * @property ?stdClass $nullableObject
 */

beforeEach(function () {
    $this->object = new stdClass();
    $this->nullableObject = null;
});

it('reads typed property from annotation', function () {
    expect($this->object)->toBeObject();
});

it('reads nullable property from annotation', function () {
    expect($this->nullableObject)->toBeNull();
});
