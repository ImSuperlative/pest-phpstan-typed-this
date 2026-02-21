<?php

namespace ImSuperlative\PestPhpstanTypedThis;

use Pest\Expectation;
use Pest\Mixins\Expectation as MixinExpectation;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Type\MixedType;

/**
 * Allows any method call on Pest Expectation classes.
 *
 * Pest expectations support higher-order method proxying (e.g.
 * expect($collection)->first()) and custom expectations added
 * via expect()->extend(). This extension tells PHPStan any
 * method exists and returns mixed.
 */
final class PestExpectationMethodExtension implements MethodsClassReflectionExtension
{
    private const EXPECTATION_CLASSES = [
        Expectation::class,
        MixinExpectation::class,
    ];

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (! in_array($classReflection->getName(), self::EXPECTATION_CLASSES, true)
            && ! is_a($classReflection->getName(), Expectation::class, true)) {
            return false;
        }

        return ! $classReflection->hasNativeMethod($methodName);
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new PestDynamicMethodReflection(
            declaringClass: $classReflection,
            methodName: $methodName,
            returnType: new MixedType(),
        );
    }
}
