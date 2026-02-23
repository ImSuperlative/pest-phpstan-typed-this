<?php

/** @noinspection ClassConstantCanBeUsedInspection */

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Type\Type;

final class PestExpectationClasses
{
    public const string EXPECTATION = 'Pest\Expectation';

    public const string EXPECTATION_MIXIN = 'Pest\Mixins\Expectation';

    public const string HIGHER_ORDER = 'Pest\Expectations\HigherOrderExpectation';

    public static function resolvePropertyType(Type $type, string $name): ?Type
    {
        return $type->hasProperty($name)->yes()
            ? $type->getProperty($name, new OutOfClassScope)->getReadableType()
            : null;
    }

    public static function resolveMethodReturnType(Type $type, string $name): ?Type
    {
        return $type->hasMethod($name)->yes()
            ? $type->getMethod($name, new OutOfClassScope)->getVariants()[0]->getReturnType()
            : null;
    }
}
