<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified;

use PhpParser\Node;
use PhpParser\Node\Scalar\Int_;

final class ToHaveLengthZeroRule extends ExpectationSimplificationRule
{
    protected function getMethodNames(): array
    {
        return ['toHaveLength'];
    }

    protected function matchesArgument(Node $arg): bool
    {
        return $arg instanceof Int_ && $arg->value === 0;
    }

    protected function describeArgument(Node $arg): string
    {
        return '0';
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