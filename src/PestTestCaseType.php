<?php

namespace ImSuperlative\PestPhpstanTypedThis;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\Type\CallbackUnresolvedPropertyPrototypeReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

/**
 * Custom ObjectType that adds writable typed dynamic properties from Pest test files.
 *
 * Extends ObjectType so $this still has all TestCase methods, but overrides
 * property resolution to include parsed properties (from @property annotations
 * and beforeEach assignments). Properties are both readable and writable.
 *
 * Uses a unique describe() to avoid conflicting with ObjectType's static property cache.
 */
final class PestTestCaseType extends ObjectType
{
    /** @param  array<string, Type>  $dynamicProperties */
    public function __construct(
        string $className,
        private array $dynamicProperties,
    ) {
        parent::__construct($className);
    }

    public function describe(VerbosityLevel $level): string
    {
        $base = parent::describe($level);

        if ($this->dynamicProperties === []) {
            return $base;
        }

        $props = [];
        foreach ($this->dynamicProperties as $name => $type) {
            $props[] = $name.': '.$type->describe($level);
        }

        return $base.'{'.implode(', ', $props).'}';
    }

    public function hasProperty(string $propertyName): TrinaryLogic
    {
        if (isset($this->dynamicProperties[$propertyName])) {
            return TrinaryLogic::createYes();
        }

        return parent::hasProperty($propertyName);
    }

    public function hasInstanceProperty(string $propertyName): TrinaryLogic
    {
        if (isset($this->dynamicProperties[$propertyName])) {
            return TrinaryLogic::createYes();
        }

        return parent::hasInstanceProperty($propertyName);
    }

    public function getUnresolvedPropertyPrototype(
        string $propertyName,
        ClassMemberAccessAnswerer $scope,
    ): UnresolvedPropertyPrototypeReflection {
        if (isset($this->dynamicProperties[$propertyName])) {
            return $this->buildPropertyPrototype($propertyName);
        }

        return parent::getUnresolvedPropertyPrototype($propertyName, $scope);
    }

    public function getUnresolvedInstancePropertyPrototype(
        string $propertyName,
        ClassMemberAccessAnswerer $scope,
    ): UnresolvedPropertyPrototypeReflection {
        if (isset($this->dynamicProperties[$propertyName])) {
            return $this->buildPropertyPrototype($propertyName);
        }

        return parent::getUnresolvedInstancePropertyPrototype($propertyName, $scope);
    }

    private function buildPropertyPrototype(string $propertyName): UnresolvedPropertyPrototypeReflection
    {
        $property = new PestPropertyReflection($propertyName, $this->dynamicProperties[$propertyName]);

        return new CallbackUnresolvedPropertyPrototypeReflection(
            $property,
            $property->getDeclaringClass(),
            false,
            static fn (Type $type): Type => $type,
        );
    }
}
