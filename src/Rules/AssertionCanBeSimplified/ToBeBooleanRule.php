<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;

final class ToBeBooleanRule extends ExpectationSimplificationRule
{
    protected function getMethodNames(): array
    {
        return ['toBe', 'toEqual'];
    }

    protected function matchesArgument(Node $arg): bool
    {
        return $arg instanceof ConstFetch
            && in_array($arg->name->toLowerString(), ['true', 'false'], true);
    }

    protected function describeArgument(Node $arg): string
    {
        /** @var ConstFetch $arg */
        return $arg->name->toLowerString();
    }

    protected function getReplacement(Node $arg): string
    {
        /** @var ConstFetch $arg */
        return $arg->name->toLowerString() === 'true' ? 'toBeTrue' : 'toBeFalse';
    }

    protected function getIdentifier(Node $arg): string
    {
        /** @var ConstFetch $arg */
        return $arg->name->toLowerString() === 'true' ? 'pest.toBeTrue' : 'pest.toBeFalse';
    }
}