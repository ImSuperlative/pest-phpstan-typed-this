<?php

/** @noinspection ClassConstantCanBeUsedInspection */

declare(strict_types=1);

namespace ImSuperlative\PhpstanPest\Expectation;

use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;

final class PestExpectationClasses
{
    public const string EXPECTATION = 'Pest\Expectation';

    public const string EXPECTATION_MIXIN = 'Pest\Mixins\Expectation';

    public const string HIGHER_ORDER = 'Pest\Expectations\HigherOrderExpectation';

    public function resolvePropertyType(Type $type, string $name): ?Type
    {
        return $type->hasProperty($name)->yes()
            ? $type->getProperty($name, new OutOfClassScope)->getReadableType()
            : null;
    }

    public function resolveMethodReturnType(Type $type, string $name): ?Type
    {
        return $type->hasMethod($name)->yes()
            ? $type->getMethod($name, new OutOfClassScope)->getVariants()[0]->getReturnType()
            : null;
    }

    public function narrowUnionForProperty(Type $type, string $name): Type
    {
        return $this->narrowUnion($type, static fn (Type $m): bool => $m->hasProperty($name)->yes());
    }

    public function narrowUnionForMethod(Type $type, string $name): Type
    {
        return $this->narrowUnion($type, static fn (Type $m): bool => $m->hasMethod($name)->yes());
    }

    public function resolveNarrowedType(Type $type, string $name): ?Type
    {
        return $this->resolvePropertyType($this->narrowUnionForProperty($type, $name), $name)
            ?? $this->resolveMethodReturnType($this->narrowUnionForMethod($type, $name), $name);
    }

    /** @param \Closure(Type): bool $predicate */
    private function narrowUnion(Type $type, \Closure $predicate): Type
    {
        if (! $type instanceof UnionType) {
            return $type;
        }

        $matching = array_filter($type->getTypes(), $predicate);

        return $matching !== [] ? TypeCombinator::union(...$matching) : $type;
    }
}
