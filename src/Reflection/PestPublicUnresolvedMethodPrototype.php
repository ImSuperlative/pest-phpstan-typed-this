<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Reflection;

use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\Type\UnresolvedMethodPrototypeReflection;
use PHPStan\Type\Type;

/**
 * Wraps an UnresolvedMethodPrototypeReflection to return a public method reflection.
 *
 * Works with PestPublicExtendedMethodReflection to make protected TestCase methods
 * accessible inside Pest closures.
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
        return $this->wrapped->getNakedMethod();
    }

    public function getTransformedMethod(): ExtendedMethodReflection
    {
        $method = $this->wrapped->getTransformedMethod();

        return new PestPublicExtendedMethodReflection($method);
    }

    public function withCalledOnType(Type $type): self
    {
        return new self($this->wrapped->withCalledOnType($type));
    }
}