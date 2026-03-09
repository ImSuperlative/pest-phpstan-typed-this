<?php

declare(strict_types=1);

namespace ImSuperlative\PhpstanPest\Expectation;

use ImSuperlative\PhpstanPest\Reflection\SimpleParameterReflection;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\ClosureType;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MethodParameterClosureTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;

final class SequenceClosureTypeExtension implements MethodParameterClosureTypeExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private PestExpectationClasses $pestExpectationClasses,
    ) {}

    public function isMethodSupported(MethodReflection $methodReflection, ParameterReflection $parameter): bool
    {
        return $methodReflection->getDeclaringClass()->getName() === PestExpectationClasses::EXPECTATION
            && $methodReflection->getName() === 'sequence';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        ParameterReflection $parameter,
        Scope $scope,
    ): ?Type {
        $iterableValueType = $this->resolveIterableValueType($methodCall, $scope);

        return $iterableValueType !== null
            ? $this->buildSequenceClosureType($iterableValueType, $this->resolveIterableKeyType($methodCall, $scope))
            : null;
    }

    private function resolveExpectationTValue(MethodCall $methodCall, Scope $scope): ?Type
    {
        $callerType = $scope->getType($methodCall->var);
        $valueType = $callerType->getTemplateType(PestExpectationClasses::EXPECTATION, 'TValue');

        return $this->nonMixed($valueType);
    }

    private function resolveIterableValueType(MethodCall $methodCall, Scope $scope): ?Type
    {
        $tValue = $this->resolveExpectationTValue($methodCall, $scope);

        return $tValue !== null ? $this->nonMixed($tValue->getIterableValueType()) : null;
    }

    private function resolveIterableKeyType(MethodCall $methodCall, Scope $scope): Type
    {
        $tValue = $this->resolveExpectationTValue($methodCall, $scope);

        return $tValue !== null ? $tValue->getIterableKeyType() : new MixedType;
    }

    private function nonMixed(Type $type): ?Type
    {
        return $type instanceof MixedType && ! $type->isExplicitMixed() ? null : $type;
    }

    private function wrapInExpectation(Type $type): ExpectationType
    {
        return new ExpectationType($type, $this->reflectionProvider, $this->pestExpectationClasses);
    }

    private function buildSequenceClosureType(Type $iterableValueType, Type $iterableKeyType): ClosureType
    {
        return new ClosureType(
            [
                new SimpleParameterReflection('value', $this->wrapInExpectation($iterableValueType)),
                new SimpleParameterReflection('key', $this->wrapInExpectation($iterableKeyType), optional: true),
            ],
            new VoidType,
        );
    }
}
