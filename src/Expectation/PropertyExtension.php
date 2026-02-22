<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use ImSuperlative\PestPhpstanTypedThis\Reflection\PestPropertyReflection;
use Pest\Expectation;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * Provides property reflections for dynamic property access on Pest Expectation.
 *
 * When accessing ->someProperty on Expectation, Pest proxies to the
 * underlying value via __get and wraps the result in HigherOrderExpectation.
 * This extension tells PHPStan the property exists and returns
 * HigherOrderExpectation with template types preserved so PHPStan can
 * resolve generics at the call site.
 */
final class PropertyExtension implements PropertiesClassReflectionExtension
{
    private const string EXPECTATION_CLASS = 'Pest\Expectation';

    private const string HIGHER_ORDER_CLASS = 'Pest\Expectations\HigherOrderExpectation';

    /** @noinspection ClassConstantCanBeUsedInspection */
    private const array EXPECTATION_CLASSES = [
        Expectation::class,
        'Pest\Mixins\Expectation',
        self::HIGHER_ORDER_CLASS,
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
        $higherOrderType = $this->buildHigherOrderType($classReflection);

        return new PestPropertyReflection(
            type: $higherOrderType,
            declaringClass: $classReflection,
        );
    }

    private function buildHigherOrderType(ClassReflection $classReflection): Type
    {
        return $classReflection->getName() === self::HIGHER_ORDER_CLASS
            ? $this->preserveHigherOrderTemplates($classReflection)
            : $this->wrapExpectationInHigherOrder($classReflection);
    }

    /** HigherOrderExpectation<TOriginalValue, TValue> → preserve both templates */
    private function preserveHigherOrderTemplates(ClassReflection $classReflection): GenericObjectType
    {
        $templateTypeMap = $classReflection->getTemplateTypeMap();
        $tOriginal = $templateTypeMap->getType('TOriginalValue') ?? new MixedType();
        $tValue = $templateTypeMap->getType('TValue') ?? new MixedType();

        return new GenericObjectType(self::HIGHER_ORDER_CLASS, [$tOriginal, $tValue]);
    }

    /** Expectation<TValue> → HigherOrderExpectation<Expectation<TValue>, TValue> */
    private function wrapExpectationInHigherOrder(ClassReflection $classReflection): GenericObjectType
    {
        $tValue = $classReflection->getTemplateTypeMap()->getType('TValue') ?? new MixedType();
        $expectationType = new GenericObjectType(self::EXPECTATION_CLASS, [$tValue]);

        return new GenericObjectType(self::HIGHER_ORDER_CLASS, [$expectationType, $tValue]);
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
}