<?php

use function PHPStan\Testing\assertType;

/**
 * @property stdClass $case
 */

beforeEach(function () {
    $this->case = new stdClass();
});

it('allows writing to typed properties', function () {
    $this->case = new stdClass();
    assertType('stdClass', $this->case);
});
