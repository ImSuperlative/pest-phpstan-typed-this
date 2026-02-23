<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use ImSuperlative\PestPhpstanTypedThis\Reflection\PestDynamicMethodReflection;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * Resolves return types for dynamic proxy methods on Pest\Expectation.
 *
 * When calling a method that doesn't exist natively on Expectation
 * (e.g. ->event(), ->first()), Pest proxies to the underlying value.
 * This extension returns HigherOrderExpectation<Expectation<TValue>, TResolved>
 * so that chained calls like ->scoped() can be properly typed.
 */
final class DynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return PestExpectationClasses::EXPECTATION;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        // Only handle methods provided by MethodExtension (dynamic proxy methods)
        return $methodReflection instanceof PestDynamicMethodReflection;
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): ?Type {
        $callerType = $scope->getType($methodCall->var);
        $valueType = $this->extractValueType($callerType);
        $methodName = $methodReflection->getName();

        $resolvedType = $valueType !== null
            ? PestExpectationClasses::resolvePropertyType($valueType, $methodName)
                ?? PestExpectationClasses::resolveMethodReturnType($valueType, $methodName)
            : null;

        return $resolvedType !== null
            ? $this->buildHigherOrderType($callerType, $resolvedType)
            : null;
    }

    private function extractValueType(Type $type): ?Type
    {
        $tValue = $type->getTemplateType(PestExpectationClasses::EXPECTATION, 'TValue');

        return $tValue instanceof MixedType && ! $tValue->isExplicitMixed()
            ? null
            : $tValue;
    }

    /**
     * Build HigherOrderExpectation<TOriginal, TResolved>
     */
    private function buildHigherOrderType(Type $originalExpectationType, Type $resolvedType): GenericObjectType
    {
        return new GenericObjectType(PestExpectationClasses::HIGHER_ORDER, [
            $originalExpectationType,
            $resolvedType,
        ]);
    }
}
