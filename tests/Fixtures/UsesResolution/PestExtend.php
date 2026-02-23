<?php

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns\HasGreeting;
use function PHPStan\Testing\assertType;

pest()->extend(HasGreeting::class);

it('can call trait methods via pest()->extend()', function () {
    assertType('string', $this->greet('world'));
});
