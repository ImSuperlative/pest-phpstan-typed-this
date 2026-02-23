<?php

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns\HasGreeting;
use function PHPStan\Testing\assertType;

pest()->use(HasGreeting::class);

it('can call trait methods via pest()->use()', function () {
    assertType('string', $this->greet('world'));
});
