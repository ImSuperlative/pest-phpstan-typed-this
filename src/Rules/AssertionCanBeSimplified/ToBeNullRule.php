<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;

final class ToBeNullRule extends ExpectationSimplificationRule
{
    protected function getMethodNames(): array
    {
        return ['toBe', 'toEqual'];
    }

    protected function matchesArgument(Node $arg): bool
    {
        return $arg instanceof ConstFetch
            && $arg->name->toLowerString() === 'null';
    }

    protected function describeArgument(Node $arg): string
    {
        return 'null';
    }

    protected function getReplacement(Node $arg): string
    {
        return 'toBeNull';
    }

    protected function getIdentifier(Node $arg): string
    {
        return 'pest.toBeNull';
    }
}