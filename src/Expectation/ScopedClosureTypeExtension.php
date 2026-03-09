<?php

declare(strict_types=1);

namespace ImSuperlative\PhpstanPest\Expectation;

use ImSuperlative\PhpstanPest\Reflection\SimpleParameterReflection;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ClosureType;
use PHPStan\Type\MethodParameterClosureTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;

final class ScopedClosureTypeExtension implements MethodParameterClosureTypeExtension
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private PestExpectationClasses $pestExpectationClasses,
    ) {}

    public function isMethodSupported(MethodReflection $methodReflection, ParameterReflection $parameter): bool
    {
        return $methodReflection->getDeclaringClass()->getName() === PestExpectationClasses::HIGHER_ORDER
            && $methodReflection->getName() === 'scoped';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        ParameterReflection $parameter,
        Scope $scope,
    ): ?Type {
        $tValue = $this->resolveHigherOrderTValue($methodCall, $scope);

        return $tValue !== null
            ? new ClosureType(
                [new SimpleParameterReflection('expectation', new ExpectationType($tValue, $this->reflectionProvider, $this->pestExpectationClasses))],
                new VoidType,
            )
            : null;
    }

    private function resolveHigherOrderTValue(MethodCall $methodCall, Scope $scope): ?Type
    {
        $callerType = $scope->getType($methodCall->var);
        $tValue = $callerType->getTemplateType(PestExpectationClasses::HIGHER_ORDER, 'TValue');

        return $tValue instanceof MixedType && ! $tValue->isExplicitMixed() ? null : $tValue;
    }
}
