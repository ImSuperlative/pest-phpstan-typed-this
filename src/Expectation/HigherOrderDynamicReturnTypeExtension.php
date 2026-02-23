<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * Resolves return types for method calls on HigherOrderExpectation.
 *
 * Pest's HigherOrderExpectation proxies method calls via __call:
 * - Assertion methods (toBe, toBeString, etc.) return self<TOriginalValue, TValue>
 * - Value proxy methods return self<TOriginalValue, ReturnType>
 *
 * Without this extension, PHPStan resolves through @-mixin and loses the
 * HigherOrderExpectation wrapper, breaking the chain.
 */
final class HigherOrderDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    // private const array NATIVE_METHODS = ['scoped', 'not', 'expect', 'and', 'json', '__call', '__get'];

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {}

    public function getClass(): string
    {
        return PestExpectationClasses::HIGHER_ORDER;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return ! $this->isNativeMethod($methodReflection->getName());
    }

    private function isNativeMethod(string $methodName): bool
    {
        return $this->reflectionProvider->hasClass(PestExpectationClasses::HIGHER_ORDER)
            && $this->reflectionProvider->getClass(PestExpectationClasses::HIGHER_ORDER)
                ->hasNativeMethod($methodName);
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): ?Type {
        $callerType = $scope->getType($methodCall->var);
        $tOriginal = $this->extractOriginalValueType($callerType);

        return $tOriginal instanceof Type
            ? $this->resolveHigherOrderType($callerType, $tOriginal, $methodReflection->getName())
            : null;
    }

    private function resolveHigherOrderType(Type $callerType, Type $tOriginal, string $methodName): GenericObjectType
    {
        $tValue = $callerType->getTemplateType(PestExpectationClasses::HIGHER_ORDER, 'TValue');

        $resolvedValue = $this->resolveAsExpectationMethod($methodName, $tValue)
            ?? PestExpectationClasses::resolveMethodReturnType($tValue, $methodName)
            ?? PestExpectationClasses::resolvePropertyType($tValue, $methodName)
            ?? new MixedType;

        return new GenericObjectType(PestExpectationClasses::HIGHER_ORDER, [$tOriginal, $resolvedValue]);
    }

    /** Assertion methods (toBe, toBeString, etc.) preserve TValue unchanged */
    private function resolveAsExpectationMethod(string $methodName, Type $tValue): ?Type
    {
        return $this->isExpectationMethod($methodName) ? $tValue : null;
    }

    private function extractOriginalValueType(Type $type): ?Type
    {
        $template = $type->getTemplateType(PestExpectationClasses::HIGHER_ORDER, 'TOriginalValue');

        return $template instanceof MixedType && ! $template->isExplicitMixed()
            ? null
            : $template;
    }

    private function isExpectationMethod(string $methodName): bool
    {
        return $this->reflectionProvider->hasClass(PestExpectationClasses::EXPECTATION)
            && $this->reflectionProvider->getClass(PestExpectationClasses::EXPECTATION)->hasMethod($methodName);
    }
}
