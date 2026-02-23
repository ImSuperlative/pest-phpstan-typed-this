<?php

namespace ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns;

trait HasSubGreeting
{
    public function subGreet(string $name): string
    {
        return 'Sub Hello, '.$name.'!';
    }
}
