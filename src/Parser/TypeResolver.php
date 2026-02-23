<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Parser;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;

final class TypeResolver
{
    public function __construct(
        private TypeNodeResolver $typeNodeResolver,
        private TypeStringParser $typeStringParser,
    ) {
    }

    /**
     * @param  array<string, string>  $useMap
     */
    public function buildNameScope(array $useMap): NameScope
    {
        return new NameScope(null, $useMap);
    }

    public function resolveTypeNode(TypeNode $typeNode, NameScope $nameScope): ?Type
    {
        $type = $this->typeNodeResolver->resolve($typeNode, $nameScope);

        return $type instanceof ErrorType ? null : $type;
    }

    public function resolveTypeString(string $typeString, NameScope $nameScope): ?Type
    {
        $typeNode = $this->typeStringParser->parseTypeString($typeString);

        if (! $typeNode instanceof TypeNode) {
            return null;
        }

        return $this->resolveTypeNode($typeNode, $nameScope);
    }
}
