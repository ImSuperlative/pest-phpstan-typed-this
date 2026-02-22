<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Reflection;

use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Reflection\Type\UnresolvedPropertyPrototypeReflection;
use PHPStan\Type\Type;

final class PestUnresolvedPropertyPrototype implements UnresolvedPropertyPrototypeReflection
{
    public function __construct(
        private ExtendedPropertyReflection $property,
    ) {}

    public function doNotResolveTemplateTypeMapToBounds(): self
    {
        return $this;
    }

    public function getNakedProperty(): ExtendedPropertyReflection
    {
        return $this->property;
    }

    public function getTransformedProperty(): ExtendedPropertyReflection
    {
        return $this->property;
    }

    public function withFechedOnType(Type $type): self
    {
        return $this;
    }
}
