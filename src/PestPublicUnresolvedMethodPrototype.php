<?php

namespace ImSuperlative\PestPhpstanTypedThis;

use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection;
use PHPStan\Type\Type;

/**
 * Wraps an UnresolvedMethodPrototypeReflection to make its methods public.
 */
final class PestPublicUnresolvedMethodPrototype implements UnresolvedMethodPrototypeReflection
{
    public function __construct(
        private UnresolvedMethodPrototypeReflection $wrapped,
    ) {}

    public function doNotResolveTemplateTypeMapToBounds(): self
    {
        return new self($this->wrapped->doNotResolveTemplateTypeMapToBounds());
    }

    public function getNakedMethod(): ExtendedMethodReflection
    {
        return new PestPublicMethodReflection($this->wrapped->getNakedMethod());
    }

    public function getTransformedMethod(): ExtendedMethodReflection
    {
        return new PestPublicMethodReflection($this->wrapped->getTransformedMethod());
    }

    public function withCalledOnType(Type $type): self
    {
        return new self($this->wrapped->withCalledOnType($type));
    }
}