<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Parser;

use ImSuperlative\PestPhpstanTypedThis\Parser\Concerns\FirstOccurrenceFilterTrait;
use ImSuperlative\PestPhpstanTypedThis\Parser\Concerns\PhpDocExtractorTrait;
use ImSuperlative\PestPhpstanTypedThis\Parser\Contracts\PropertyParserStrategy;
use PhpParser\Node;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

final class PhpDocPropertyParser implements PropertyParserStrategy
{
    use PhpDocExtractorTrait;
    use FirstOccurrenceFilterTrait;

    public function __construct(
        private PhpDocStringResolver $phpDocStringResolver,
        private TypeResolver $typeResolver,
    ) {}

    private function getPhpDocStringResolver(): PhpDocStringResolver
    {
        return $this->phpDocStringResolver;
    }

    public function parse(array $stmts, array $useMap, array $existingProperties, string $filePath): array
    {
        $tags = $this->findPropertyTags($stmts);
        $definitions = $this->extractDefinitions($tags, $existingProperties);
        $nameScope = $this->typeResolver->buildNameScope($useMap);

        return array_filter(array_map(
            fn (TypeNode $typeNode) => $this->typeResolver->resolveTypeNode($typeNode, $nameScope),
            $definitions,
        ));
    }

    /**
     * Collect all @property, @property-read, @property-write tags from statements.
     *
     * @param  Node\Stmt[]  $stmts
     * @return array<PropertyTagValueNode>
     */
    public function findPropertyTags(array $stmts): array
    {
        $tags = [];

        foreach ($this->extractPhpDocNodes($stmts) as $phpDocNode) {
            $tags = [...$tags, ...$this->getPropertyTagValues($phpDocNode)];
        }

        return $tags;
    }

    /**
     * @return array<PropertyTagValueNode>
     */
    public function getPropertyTagValues(PhpDocNode $phpDocNode): array
    {
        return [
            ...$phpDocNode->getPropertyTagValues(),
            ...$phpDocNode->getPropertyReadTagValues(),
            ...$phpDocNode->getPropertyWriteTagValues(),
        ];
    }

    /**
     * Map tag nodes to name => TypeNode, keeping only the first occurrence per name.
     *
     * @param  array<PropertyTagValueNode>  $tags
     * @param  array<string, mixed>  $existingProperties
     * @return array<string, TypeNode>
     */
    public function extractDefinitions(array $tags, array $existingProperties = []): array
    {
        $pairs = array_map(
            static fn (PropertyTagValueNode $tag) => [ltrim($tag->propertyName, '$'), $tag->type],
            $tags,
        );

        return $this->collectFirstOccurrences($pairs, $existingProperties);
    }
}
