<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Expectation;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\OutOfClassScope;
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
    private const string EXPECTATION_CLASS = 'Pest\Expectation';

    private const string HIGHER_ORDER_CLASS = 'Pest\Expectations\HigherOrderExpectation';

    private const array NATIVE_METHODS = ['scoped', 'not', 'expect', 'and', 'json', '__call', '__get'];

    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {}

    public function getClass(): string
    {
        return self::HIGHER_ORDER_CLASS;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return ! in_array($methodReflection->getName(), self::NATIVE_METHODS, true);
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): ?Type {
        $callerType = $scope->getType($methodCall->var);
        $tOriginal = $this->extractTemplateType($callerType, 'TOriginalValue');

        return $tOriginal !== null
            ? $this->resolveHigherOrderType($callerType, $tOriginal, $methodReflection->getName())
            : null;
    }

    private function resolveHigherOrderType(Type $callerType, Type $tOriginal, string $methodName): GenericObjectType
    {
        $tValue = $callerType->getTemplateType(self::HIGHER_ORDER_CLASS, 'TValue');

        $resolvedValue = $this->resolveAsExpectationMethod($methodName, $tValue)
            ?? $this->resolveMethodReturnType($tValue, $methodName)
            ?? $this->resolvePropertyType($tValue, $methodName)
            ?? new MixedType();

        return new GenericObjectType(self::HIGHER_ORDER_CLASS, [$tOriginal, $resolvedValue]);
    }

    /** Assertion methods (toBe, toBeString, etc.) preserve TValue unchanged */
    private function resolveAsExpectationMethod(string $methodName, Type $tValue): ?Type
    {
        return $this->isExpectationMethod($methodName) ? $tValue : null;
    }

    private function resolveMethodReturnType(Type $valueType, string $name): ?Type
    {
        return $valueType->hasMethod($name)->yes()
            ? $valueType->getMethod($name, new OutOfClassScope())->getVariants()[0]->getReturnType()
            : null;
    }

    private function resolvePropertyType(Type $valueType, string $name): ?Type
    {
        return $valueType->hasProperty($name)->yes()
            ? $valueType->getProperty($name, new OutOfClassScope())->getReadableType()
            : null;
    }

    private function extractTemplateType(Type $type, string $templateName): ?Type
    {
        $template = $type->getTemplateType(self::HIGHER_ORDER_CLASS, $templateName);

        return $template instanceof MixedType && ! $template->isExplicitMixed()
            ? null
            : $template;
    }

    private function isExpectationMethod(string $methodName): bool
    {
        return $this->reflectionProvider->hasClass(self::EXPECTATION_CLASS)
            && $this->reflectionProvider->getClass(self::EXPECTATION_CLASS)->hasMethod($methodName);
    }
}
