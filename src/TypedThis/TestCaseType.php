<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

use ImSuperlative\PestPhpstanTypedThis\Reflection\PestPropertyReflection;
use ImSuperlative\PestPhpstanTypedThis\Reflection\PestPublicUnresolvedMethodPrototype;
use ImSuperlative\PestPhpstanTypedThis\Reflection\PestUnresolvedPropertyPrototype;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\Reflection\WrappedExtendedPropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use stdClass;

/**
 * Extended ObjectType that adds typed dynamic properties to the TestCase.
 *
 * Returned by ClosureThisExtension as the $this type inside Pest closures.
 * Delegates to the parent TestCase for native properties and methods, while
 * intercepting access to dynamic properties parsed from the test file.
 */
final class TestCaseType extends ObjectType
{
    /** @param array<string, Type> $dynamicProperties */
    public function __construct(
        string $className,
        private array $dynamicProperties,
        private ReflectionProvider $reflectionProvider,
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

    public function getUnresolvedMethodPrototype(
        string $methodName,
        ClassMemberAccessAnswerer $scope,
    ): UnresolvedMethodPrototypeReflection {
        $prototype = parent::getUnresolvedMethodPrototype($methodName, $scope);

        // Wrap as public so protected TestCase methods (mock, assertDatabaseHas, etc.)
        // are accessible inside Pest closures where PHPStan doesn't consider the
        // scope as "inside" the TestCase class.
        return new PestPublicUnresolvedMethodPrototype($prototype);
    }

    private function buildPropertyPrototype(string $propertyName): UnresolvedPropertyPrototypeReflection
    {
        $declaringClass = $this->reflectionProvider->getClass(stdClass::class);
        $property = new PestPropertyReflection($this->dynamicProperties[$propertyName], $declaringClass);
        $extended = new WrappedExtendedPropertyReflection($propertyName, $property);

        return new PestUnresolvedPropertyPrototype($extended);
    }
}