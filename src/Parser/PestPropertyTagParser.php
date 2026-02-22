<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Parser;

use ImSuperlative\PestPhpstanTypedThis\Parser\Concerns\FirstOccurrenceFilterTrait;
use ImSuperlative\PestPhpstanTypedThis\Parser\Concerns\PhpDocExtractorTrait;
use ImSuperlative\PestPhpstanTypedThis\Parser\Contracts\PropertyParserStrategy;
use PhpParser\Node;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\TokenIterator;

final class PestPropertyTagParser implements PropertyParserStrategy
{
    use PhpDocExtractorTrait;
    use FirstOccurrenceFilterTrait;

    public function __construct(
        private TypeStringParser $typeStringParser,
        private PhpDocStringResolver $phpDocStringResolver,
        private TypeResolver $typeResolver,
    ) {}

    private function getPhpDocStringResolver(): PhpDocStringResolver
    {
        return $this->phpDocStringResolver;
    }

    public function parse(array $stmts, array $useMap, array $existingProperties, string $filePath): array
    {
        $definitions = $this->extractDefinitions($stmts, $existingProperties);
        $nameScope = $this->typeResolver->buildNameScope($useMap);

        return array_filter(array_map(
            fn (TypeNode $typeNode) => $this->typeResolver->resolveTypeNode($typeNode, $nameScope),
            $definitions,
        ));
    }

    /**
     * Find all @pest-property tags and extract name => TypeNode definitions.
     *
     * @param  Node\Stmt[]  $stmts
     * @param  array<string, mixed>  $existingProperties
     * @return array<string, TypeNode>
     */
    public function extractDefinitions(array $stmts, array $existingProperties = []): array
    {
        $pairs = array_filter(array_map(
            fn (string $raw) => $this->parseTagValue($raw),
            $this->findRawTagValues($stmts),
        ));

        return $this->collectFirstOccurrences($pairs, $existingProperties);
    }

    /**
     * Collect raw @pest-property tag value strings from all statements.
     *
     * @param  Node\Stmt[]  $stmts
     * @return array<string>
     */
    public function findRawTagValues(array $stmts): array
    {
        $tags = array_reduce(
            $this->extractPhpDocNodes($stmts),
            fn (array $carry, $node) => [...$carry, ...$node->getTagsByName('@pest-property')],
            [],
        );

        return array_map(fn ($tag) => trim((string) $tag->value), $tags);
    }

    /**
     * Parse a `@pest-property` tag value into a [name, typeNode] pair.
     *
     * @return array{string, TypeNode}|null
     */
    public function parseTagValue(string $rawValue): ?array
    {
        $typeNode = $this->parseType($rawValue);
        $name = $this->extractVariableName($rawValue);

        return $typeNode !== null && $name !== null ? [$name, $typeNode] : null;
    }

    private function parseType(string $rawValue): ?TypeNode
    {
        return $rawValue !== '' ? $this->typeStringParser->parseTypeString($rawValue) : null;
    }

    /**
     * Extract the `$variable` name that follows the type in a tag value.
     */
    public function extractVariableName(string $rawValue): ?string
    {
        $tokens = $this->tokenize($rawValue);

        if ($tokens === null) {
            return null;
        }

        return $tokens->isCurrentTokenType(Lexer::TOKEN_VARIABLE)
            ? ltrim($tokens->currentTokenValue(), '$')
            : null;
    }

    /**
     * Tokenize the raw value, consume the type, and advance past whitespace.
     */
    private function tokenize(string $rawValue): ?TokenIterator
    {
        $tokens = new TokenIterator($this->typeStringParser->getLexer()->tokenize($rawValue));

        try {
            $this->typeStringParser->getTypeParser()->parse($tokens);
        } catch (ParserException) {
            return null;
        }

        $tokens->tryConsumeTokenType(Lexer::TOKEN_HORIZONTAL_WS);

        return $tokens;
    }
}
