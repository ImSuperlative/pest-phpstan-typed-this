<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use ImSuperlative\PestPhpstanTypedThis\Reflection\PestPropertyReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * Provides property reflections for dynamic property access on HigherOrderExpectation.
 *
 * When accessing ->someProperty on HigherOrderExpectation, Pest proxies to the
 * underlying value via __get and wraps the result in HigherOrderExpectation.
 * This extension tells PHPStan the property exists and returns
 * HigherOrderExpectation with template types preserved so PHPStan can
 * resolve generics at the call site.
 */
final class PropertyExtension implements PropertiesClassReflectionExtension
{
    private const array EXPECTATION_CLASSES = [
        PestExpectationClasses::EXPECTATION,
        PestExpectationClasses::EXPECTATION_MIXIN,
        PestExpectationClasses::HIGHER_ORDER,
    ];

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {}

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $this->isExpectationClass($classReflection)
            && ! $this->isNativeOrValueProperty($classReflection, $propertyName);
    }

    private function isNativeOrValueProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return $classReflection->hasNativeProperty($propertyName) || $propertyName === 'value';
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new PestPropertyReflection(
            type: $this->buildHigherOrderType($classReflection),
            declaringClass: $classReflection,
        );
    }

    private function buildHigherOrderType(ClassReflection $classReflection): Type
    {
        return $classReflection->getName() === PestExpectationClasses::HIGHER_ORDER
            ? $this->preserveHigherOrderTemplates($classReflection)
            : $this->wrapExpectationInHigherOrder($classReflection);
    }

    /** HigherOrderExpectation<TOriginalValue, TValue> → preserve both templates */
    private function preserveHigherOrderTemplates(ClassReflection $classReflection): GenericObjectType
    {
        $templateTypeMap = $classReflection->getTemplateTypeMap();
        $tOriginal = $templateTypeMap->getType('TOriginalValue') ?? new MixedType();
        $tValue = $templateTypeMap->getType('TValue') ?? new MixedType();

        return new GenericObjectType(PestExpectationClasses::HIGHER_ORDER, [$tOriginal, $tValue]);
    }

    /** Expectation<TValue> → HigherOrderExpectation<Expectation<TValue>, TValue> */
    private function wrapExpectationInHigherOrder(ClassReflection $classReflection): GenericObjectType
    {
        $tValue = $classReflection->getTemplateTypeMap()->getType('TValue') ?? new MixedType();
        $expectationType = new GenericObjectType(PestExpectationClasses::EXPECTATION, [$tValue]);

        return new GenericObjectType(PestExpectationClasses::HIGHER_ORDER, [$expectationType, $tValue]);
    }

    private function isExpectationClass(ClassReflection $classReflection): bool
    {
        return in_array($classReflection->getName(), self::EXPECTATION_CLASSES, true)
            || $this->isExpectationSubclass($classReflection);
    }

    private function isExpectationSubclass(ClassReflection $classReflection): bool
    {
        return $this->reflectionProvider->hasClass(PestExpectationClasses::EXPECTATION)
            && $classReflection->isSubclassOfClass(
                $this->reflectionProvider->getClass(PestExpectationClasses::EXPECTATION));
    }
}
