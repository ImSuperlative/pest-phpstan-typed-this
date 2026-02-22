<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use ImSuperlative\PestPhpstanTypedThis\Reflection\PestDynamicMethodReflection;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\OutOfClassScope;
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
    private const string EXPECTATION_CLASS = 'Pest\Expectation';

    private const string HIGHER_ORDER_CLASS = 'Pest\Expectations\HigherOrderExpectation';

    public function getClass(): string
    {
        return self::EXPECTATION_CLASS;
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
            ? $this->resolvePropertyType($valueType, $methodName)
                ?? $this->resolveMethodReturnType($valueType, $methodName)
            : null;

        return $resolvedType !== null
            ? $this->buildHigherOrderType($callerType, $resolvedType)
            : null;
    }

    private function resolvePropertyType(Type $valueType, string $name): ?Type
    {
        return $valueType->hasProperty($name)->yes()
            ? $valueType->getProperty($name, new OutOfClassScope())->getReadableType()
            : null;
    }

    private function resolveMethodReturnType(Type $valueType, string $name): ?Type
    {
        return $valueType->hasMethod($name)->yes()
            ? $valueType->getMethod($name, new OutOfClassScope())->getVariants()[0]->getReturnType()
            : null;
    }

    private function extractValueType(Type $type): ?Type
    {
        $tValue = $type->getTemplateType(self::EXPECTATION_CLASS, 'TValue');

        return $tValue instanceof MixedType && ! $tValue->isExplicitMixed()
            ? null
            : $tValue;
    }

    /**
     * Build HigherOrderExpectation<TOriginal, TResolved>
     */
    private function buildHigherOrderType(Type $originalExpectationType, Type $resolvedType): GenericObjectType
    {
        return new GenericObjectType(self::HIGHER_ORDER_CLASS, [
            $originalExpectationType,
            $resolvedType,
        ]);
    }
}