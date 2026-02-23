<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use ImSuperlative\PestPhpstanTypedThis\Reflection\PestPropertyReflection;
use ImSuperlative\PestPhpstanTypedThis\Reflection\PestUnresolvedPropertyPrototype;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\Reflection\WrappedExtendedPropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use stdClass;

/**
 * Custom GenericObjectType for Pest\Expectation that resolves property access
 * on the underlying TValue type.
 *
 * When accessing ->someProperty on expect($value), this type resolves the
 * property type from the concrete TValue and returns
 * HigherOrderExpectation<Expectation<TValue>, PropertyType>.
 */
final class ExpectationType extends GenericObjectType
{
    public function __construct(
        private Type $valueType,
        private ReflectionProvider $reflectionProvider,
    ) {
        parent::__construct(PestExpectationClasses::EXPECTATION, [$valueType]);
    }

    public function describe(VerbosityLevel $level): string
    {
        return 'Expectation<'.$this->valueType->describe($level).'>';
    }

    public function hasProperty(string $propertyName): TrinaryLogic
    {
        return $this->isValueProperty($propertyName)
            ? TrinaryLogic::createYes()
            : parent::hasProperty($propertyName);
    }

    public function hasInstanceProperty(string $propertyName): TrinaryLogic
    {
        return $this->isValueProperty($propertyName)
            ? TrinaryLogic::createYes()
            : parent::hasInstanceProperty($propertyName);
    }

    public function getUnresolvedPropertyPrototype(
        string $propertyName,
        ClassMemberAccessAnswerer $scope,
    ): UnresolvedPropertyPrototypeReflection {
        $propertyType = $this->resolveValuePropertyType($propertyName);

        return $propertyType !== null
            ? $this->buildHigherOrderPropertyPrototype($propertyType)
            : parent::getUnresolvedPropertyPrototype($propertyName, $scope);
    }

    public function getUnresolvedInstancePropertyPrototype(
        string $propertyName,
        ClassMemberAccessAnswerer $scope,
    ): UnresolvedPropertyPrototypeReflection {
        $propertyType = $this->resolveValuePropertyType($propertyName);

        return $propertyType !== null
            ? $this->buildHigherOrderPropertyPrototype($propertyType)
            : parent::getUnresolvedInstancePropertyPrototype($propertyName, $scope);
    }

    private function isValueProperty(string $propertyName): bool
    {
        return $propertyName !== 'value' && $this->valueType->hasProperty($propertyName)->yes();
    }

    private function resolveValuePropertyType(string $propertyName): ?Type
    {
        return $propertyName !== 'value'
            ? PestExpectationClasses::resolvePropertyType($this->valueType, $propertyName)
            : null;
    }

    private function buildHigherOrderPropertyPrototype(Type $propertyType): UnresolvedPropertyPrototypeReflection
    {
        $expectationType = new GenericObjectType(PestExpectationClasses::EXPECTATION, [$this->valueType]);
        $higherOrderType = new GenericObjectType(
            PestExpectationClasses::HIGHER_ORDER,
            [$expectationType, $propertyType]
        );

        $declaringClass = $this->reflectionProvider->getClass(stdClass::class);
        $property = new PestPropertyReflection($higherOrderType, $declaringClass);
        $extended = new WrappedExtendedPropertyReflection('property', $property);

        return new PestUnresolvedPropertyPrototype($extended);
    }
}
