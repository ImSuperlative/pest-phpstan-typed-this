<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

use ImSuperlative\PestPhpstanTypedThis\Reflection\PestPropertyReflection;
use ImSuperlative\PestPhpstanTypedThis\Reflection\PestPublicUnresolvedMethodPrototype;
use ImSuperlative\PestPhpstanTypedThis\Reflection\PestUnresolvedPropertyPrototype;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\Reflection\WrappedExtendedPropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
/**
 * Extended ObjectType that adds typed dynamic properties to the TestCase.
 *
 * Returned by ClosureThisExtension as the $this type inside Pest closures.
 * Delegates to the parent TestCase for native properties and methods, while
 * intercepting access to dynamic properties parsed from the test file.
 */
final class TestCaseType extends ObjectType
{
    /**
     * @param  array<string, Type>  $dynamicProperties
     * @param  list<ClassReflection>  $traits  Resolved trait reflections from uses() calls
     */
    public function __construct(
        string $className,
        private array $dynamicProperties,
        private ReflectionProvider $reflectionProvider,
        private array $traits = [],
    ) {
        parent::__construct($className);
    }

    public function describe(VerbosityLevel $level): string
    {
        $base = parent::describe($level);

        if ($this->dynamicProperties === []) {
            return 'Pest<'.$base.'>';
        }

        $props = implode(', ', array_map(
            static fn (string $name, Type $type): string => $name.': '.$type->describe($level),
            array_keys($this->dynamicProperties),
            $this->dynamicProperties,
        ));

        return 'Pest<'.$base.'{'.$props.'}>';
    }

    public function hasProperty(string $propertyName): TrinaryLogic
    {
        return isset($this->dynamicProperties[$propertyName])
            ? TrinaryLogic::createYes()
            : parent::hasProperty($propertyName);
    }

    public function hasInstanceProperty(string $propertyName): TrinaryLogic
    {
        return isset($this->dynamicProperties[$propertyName])
            ? TrinaryLogic::createYes()
            : parent::hasInstanceProperty($propertyName);
    }

    public function getUnresolvedPropertyPrototype(
        string $propertyName,
        ClassMemberAccessAnswerer $scope,
    ): UnresolvedPropertyPrototypeReflection {
        return isset($this->dynamicProperties[$propertyName])
            ? $this->buildPropertyPrototype($propertyName)
            : parent::getUnresolvedPropertyPrototype($propertyName, $scope);
    }

    public function getUnresolvedInstancePropertyPrototype(
        string $propertyName,
        ClassMemberAccessAnswerer $scope,
    ): UnresolvedPropertyPrototypeReflection {
        return isset($this->dynamicProperties[$propertyName])
            ? $this->buildPropertyPrototype($propertyName)
            : parent::getUnresolvedInstancePropertyPrototype($propertyName, $scope);
    }

    public function hasMethod(string $methodName): TrinaryLogic
    {
        return array_any($this->traits, static fn (ClassReflection $trait) => $trait->hasMethod($methodName))
            ? TrinaryLogic::createYes()
            : parent::hasMethod($methodName);
    }

    public function getUnresolvedMethodPrototype(
        string $methodName,
        ClassMemberAccessAnswerer $scope,
    ): UnresolvedMethodPrototypeReflection {
        $trait = $this->findTraitWithMethod($methodName);

        $prototype = $trait instanceof ClassReflection
            ? new ObjectType($trait->getName())->getUnresolvedMethodPrototype($methodName, $scope)
            : parent::getUnresolvedMethodPrototype($methodName, $scope);

        return new PestPublicUnresolvedMethodPrototype($prototype);
    }

    private function findTraitWithMethod(string $methodName): ?ClassReflection
    {
        return array_find(
            $this->traits,
            static fn (ClassReflection $trait) => $trait->hasMethod($methodName),
        );
    }

    private function buildPropertyPrototype(string $propertyName): UnresolvedPropertyPrototypeReflection
    {
        $declaringClass = $this->reflectionProvider->getClass($this->getClassName());
        $property = new PestPropertyReflection($this->dynamicProperties[$propertyName], $declaringClass);
        $extended = new WrappedExtendedPropertyReflection($propertyName, $property);

        return new PestUnresolvedPropertyPrototype($extended);
    }
}
