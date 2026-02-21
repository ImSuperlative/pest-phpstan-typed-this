<?php

namespace ImSuperlative\PestPhpstanTypedThis;

use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\Assertions;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ExtendedParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

/**
 * Wraps an ExtendedMethodReflection to report it as public.
 *
 * In Pest closures, $this is bound to the TestCase instance,
 * so protected methods should be accessible.
 */
final class PestPublicMethodReflection implements ExtendedMethodReflection
{
    public function __construct(
        private ExtendedMethodReflection $wrapped,
    ) {}

    public function isPublic(): bool
    {
        return true;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isStatic(): bool
    {
        return $this->wrapped->isStatic();
    }

    public function getName(): string
    {
        return $this->wrapped->getName();
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->wrapped->getDeclaringClass();
    }

    public function getPrototype(): ClassMemberReflection
    {
        return $this->wrapped->getPrototype();
    }

    /** @return list<ExtendedParametersAcceptor> */
    public function getVariants(): array
    {
        return $this->wrapped->getVariants();
    }

    public function getOnlyVariant(): ExtendedParametersAcceptor
    {
        return $this->wrapped->getOnlyVariant();
    }

    /** @return list<ExtendedParametersAcceptor>|null */
    public function getNamedArgumentsVariants(): ?array
    {
        return $this->wrapped->getNamedArgumentsVariants();
    }

    public function acceptsNamedArguments(): TrinaryLogic
    {
        return $this->wrapped->acceptsNamedArguments();
    }

    public function getAsserts(): Assertions
    {
        return $this->wrapped->getAsserts();
    }

    public function getSelfOutType(): ?Type
    {
        return $this->wrapped->getSelfOutType();
    }

    public function returnsByReference(): TrinaryLogic
    {
        return $this->wrapped->returnsByReference();
    }

    public function isFinalByKeyword(): TrinaryLogic
    {
        return $this->wrapped->isFinalByKeyword();
    }

    public function isAbstract(): bool
    {
        return $this->wrapped->isAbstract();
    }

    public function isBuiltin(): bool
    {
        return $this->wrapped->isBuiltin();
    }

    public function isPure(): TrinaryLogic
    {
        return $this->wrapped->isPure();
    }

    /** @return list<\PHPStan\Reflection\AttributeReflection> */
    public function getAttributes(): array
    {
        return $this->wrapped->getAttributes();
    }

    public function mustUseReturnValue(): TrinaryLogic
    {
        return $this->wrapped->mustUseReturnValue();
    }

    public function getResolvedPhpDoc(): ?ResolvedPhpDocBlock
    {
        return $this->wrapped->getResolvedPhpDoc();
    }

    public function isDeprecated(): TrinaryLogic
    {
        return $this->wrapped->isDeprecated();
    }

    public function getDeprecatedDescription(): ?string
    {
        return $this->wrapped->getDeprecatedDescription();
    }

    public function isFinal(): TrinaryLogic
    {
        return $this->wrapped->isFinal();
    }

    public function isInternal(): TrinaryLogic
    {
        return $this->wrapped->isInternal();
    }

    public function getThrowType(): ?Type
    {
        return $this->wrapped->getThrowType();
    }

    public function hasSideEffects(): TrinaryLogic
    {
        return $this->wrapped->hasSideEffects();
    }

    public function getDocComment(): ?string
    {
        return $this->wrapped->getDocComment();
    }
}