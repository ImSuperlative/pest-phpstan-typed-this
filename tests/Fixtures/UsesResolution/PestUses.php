<?php

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns\HasGreeting;
use function PHPStan\Testing\assertType;

pest()->uses(HasGreeting::class);

it('can call trait methods via pest()->uses()', function () {
    assertType('string', $this->greet('world'));
});
