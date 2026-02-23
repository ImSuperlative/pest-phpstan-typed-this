<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use ImSuperlative\PestPhpstanTypedThis\Reflection\PestDynamicMethodReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;

final class MethodExtension implements MethodsClassReflectionExtension
{
    private const array EXPECTATION_CLASSES = [
        PestExpectationClasses::EXPECTATION,
        PestExpectationClasses::EXPECTATION_MIXIN,
    ];

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {}

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        return ! $classReflection->hasNativeMethod($methodName)
            && $this->isExpectationClass($classReflection);
    }

    private function isExpectationClass(ClassReflection $classReflection): bool
    {
        return in_array($classReflection->getName(), self::EXPECTATION_CLASSES, true)
            || $this->isExpectationSubclass($classReflection);
    }

    private function isExpectationSubclass(ClassReflection $classReflection): bool
    {
        return $this->reflectionProvider->hasClass(PestExpectationClasses::EXPECTATION)
            && $classReflection->isSubclassOfClass($this->reflectionProvider->getClass(PestExpectationClasses::EXPECTATION));
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
