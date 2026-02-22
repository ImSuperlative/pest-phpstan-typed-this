<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Reflection;

use PHPStan\BetterReflection\Reflection\ReflectionAttribute;
use PHPStan\Reflection\Assertions;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ExtendedParametersAcceptor;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

/**
 * Wraps an ExtendedMethodReflection but reports it as public.
 *
 * Used by PestPublicUnresolvedMethodPrototype to expose protected
 * TestCase methods inside Pest closures.
 */
final class PestPublicExtendedMethodReflection implements ExtendedMethodReflection
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

    public function getDeclaringClass(): ClassReflection
    {
        return $this->wrapped->getDeclaringClass();
    }

    public function isStatic(): bool
    {
        return $this->wrapped->isStatic();
    }

    public function getDocComment(): ?string
    {
        return $this->wrapped->getDocComment();
    }

    public function getName(): string
    {
        return $this->wrapped->getName();
    }

    public function getPrototype(): ExtendedMethodReflection
    {
        return $this;
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

    public function isAbstract(): TrinaryLogic
    {
        return $this->wrapped->isAbstract();
    }

    public function isBuiltin(): TrinaryLogic
    {
        return $this->wrapped->isBuiltin();
    }

    public function isPure(): TrinaryLogic
    {
        return $this->wrapped->isPure();
    }

    /** @return list<ReflectionAttribute> */
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
}
