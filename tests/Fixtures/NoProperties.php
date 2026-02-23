<?php

use function PHPStan\Testing\assertType;

it('works without any dynamic properties', function () {
    assertType('true', true);
});
