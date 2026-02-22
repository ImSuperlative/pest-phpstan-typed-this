<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use ImSuperlative\PestPhpstanTypedThis\Reflection\PestDynamicMethodReflection;
use Pest\Expectation;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;

final class MethodExtension implements MethodsClassReflectionExtension
{
    /** @noinspection ClassConstantCanBeUsedInspection */
    private const array EXPECTATION_CLASSES = [
        Expectation::class,
        'Pest\Mixins\Expectation',
    ];

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

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
        return $this->reflectionProvider->hasClass(Expectation::class)
            && $classReflection->isSubclassOfClass($this->reflectionProvider->getClass(Expectation::class));
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