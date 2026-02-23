<?php

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns\HasGreeting;
use function PHPStan\Testing\assertType;

uses(HasGreeting::class);

it('can call trait methods via uses()', function () {
    assertType('string', $this->greet('world'));
});
