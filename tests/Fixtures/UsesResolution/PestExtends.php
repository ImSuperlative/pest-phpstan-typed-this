<?php

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns\HasGreeting;
use function PHPStan\Testing\assertType;

pest()->extends(HasGreeting::class);

it('can call trait methods via pest()->extends()', function () {
    assertType('string', $this->greet('world'));
});
