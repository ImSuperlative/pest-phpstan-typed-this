<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Reflection;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Type;

/**
 * A method reflection that accepts any arguments and returns a given type.
 * Used for dynamic methods on Pest Expectation (via __call proxy).
 */
final class PestDynamicMethodReflection implements MethodReflection
{
    /** @param list<ParameterReflection> $parameters */
    public function __construct(
        private ClassReflection $declaringClass,
        private string $methodName,
        private Type $returnType,
        private array $parameters = [],
    ) {}

    public function getName(): string
    {
        return $this->methodName;
    }

    public function getPrototype(): MethodReflection
    {
        return $this;
    }

    /** @return list<FunctionVariant> */
    public function getVariants(): array
    {
        return [
            new FunctionVariant(
                templateTypeMap: TemplateTypeMap::createEmpty(),
                resolvedTemplateTypeMap: null,
                parameters: $this->parameters,
                isVariadic: $this->parameters === [],
                returnType: $this->returnType,
            ),
        ];
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getThrowType(): ?Type
    {
        return null;
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return TrinaryLogic::createMaybe();
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getDocComment(): ?string
    {
        return null;
    }
}