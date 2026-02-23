<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;

final class ToBeEmptyRule extends ExpectationSimplificationRule
{
    protected function getMethodNames(): array
    {
        return ['toBe', 'toEqual'];
    }

    protected function matchesArgument(Node $arg): bool
    {
        return $arg instanceof Array_ && $arg->items === [];
    }

    protected function describeArgument(Node $arg): string
    {
        return '[]';
    }

    protected function getReplacement(Node $arg): string
    {
        return 'toBeEmpty';
    }

    protected function getIdentifier(Node $arg): string
    {
        return 'pest.toBeEmpty';
    }
}