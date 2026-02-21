<?php

namespace ImSuperlative\PestPhpstanTypedThis;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Reflection\ReflectionProviderStaticAccessor;
use PHPStan\ShouldNotHappenException;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use stdClass;

/**
 * A writable, readable property reflection for Pest dynamic properties.
 *
 * Unlike ObjectShapePropertyReflection (read-only), this allows writes
 * so $this->prop = ... works in beforeEach closures.
 */
final class PestPropertyReflection implements ExtendedPropertyReflection
{
    public function __construct(
        private string $name,
        private Type $type,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return ReflectionProviderStaticAccessor::getInstance()->getClass(stdClass::class);
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

    public function hasPhpDocType(): bool
    {
        return true;
    }

    public function getPhpDocType(): Type
    {
        return $this->type;
    }

    public function hasNativeType(): bool
    {
        return false;
    }

    public function getNativeType(): Type
    {
        return new MixedType();
    }

    public function getReadableType(): Type
    {
        return $this->type;
    }

    public function getWritableType(): Type
    {
        return $this->type;
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return false;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isAbstract(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isFinalByKeyword(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isFinal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function isVirtual(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function hasHook(string $hookType): bool
    {
        return false;
    }

    public function getHook(string $hookType): ExtendedMethodReflection
    {
        throw new ShouldNotHappenException();
    }

    public function isProtectedSet(): bool
    {
        return false;
    }

    public function isPrivateSet(): bool
    {
        return false;
    }

    /** @return list<never> */
    public function getAttributes(): array
    {
        return [];
    }

    public function isDummy(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }
}
