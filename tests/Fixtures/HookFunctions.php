<?php

/**
 * @property stdClass $object
 */

beforeEach(function () {
    $this->object = new stdClass();
});

afterEach(function () {
    $this->object = new stdClass();
});

it('has typed properties across hooks', function () {
    expect($this->object)->toBeObject();
});
