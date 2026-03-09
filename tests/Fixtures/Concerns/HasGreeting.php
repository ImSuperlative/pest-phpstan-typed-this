<?php

namespace ImSuperlative\PhpstanPest\Tests\Fixtures\Concerns;

trait HasGreeting
{
    public function greet(string $name): string
    {
        return 'Hello, '.$name.'!';
    }
}
