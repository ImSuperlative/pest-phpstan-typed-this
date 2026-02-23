<?php

use function PHPStan\Testing\assertType;

beforeEach(function () {
    $this->object = new stdClass();
});

it('infers type from new expression', function () {
    assertType('stdClass', $this->object);
});
