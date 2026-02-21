<?php

/**
 * @property stdClass $object
 */

beforeEach(function () {
    $this->object = new stdClass();
});

describe('nested group', function () {
    it('has access to typed properties in describe block', function () {
        expect($this->object)->toBeObject();
    });
});
