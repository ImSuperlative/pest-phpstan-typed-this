<?php

/**
 * @property stdClass $case
 */

beforeEach(function () {
    $this->case = new stdClass();
});

it('allows writing to typed properties', function () {
    $this->case = new stdClass();
    expect($this->case)->toBeObject();
});
