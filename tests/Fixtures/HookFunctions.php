<?php

/**
 * @property stdClass $object
 */

beforeAll(function () {
    // $this is available in beforeAll
});

beforeEach(function () {
    $this->object = new stdClass();
});

afterEach(function () {
    $this->object = new stdClass();
});

afterAll(function () {
    // $this is available in afterAll
});

it('has typed properties across hooks', function () {
    expect($this->object)->toBeObject();
});
