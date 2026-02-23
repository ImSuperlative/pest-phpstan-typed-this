<?php

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns\HasGreeting;
use function PHPStan\Testing\assertType;

pest()->group('greeting')->extend(HasGreeting::class);

it('can call trait methods via pest()->group()->extend()', function () {
    assertType('string', $this->greet('world'));
});
