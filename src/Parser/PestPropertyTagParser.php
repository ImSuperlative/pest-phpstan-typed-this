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
     * Tokenizes once, then extracts both the type and variable name
     * from the same token stream.
     *
     * @return array{string, TypeNode}|null
     */
    public function parseTagValue(string $rawValue): ?array
    {
        if ($rawValue === '') {
            return null;
        }

        $tokens = new TokenIterator($this->typeStringParser->getLexer()->tokenize($rawValue));
        $typeNode = $this->parseType($tokens);
        $name = $typeNode !== null ? $this->extractVariableName($tokens) : null;

        return $typeNode instanceof TypeNode && $name !== null ? [$name, $typeNode] : null;
    }

    /**
     * Consume and return the type from the token stream.
     */
    public function parseType(TokenIterator $tokens): ?TypeNode
    {
        try {
            return $this->typeStringParser->getTypeParser()->parse($tokens);
        } catch (ParserException) {
            return null;
        }
    }

    /**
     * Extract the `$variable` name that follows the type in the token stream.
     */
    public function extractVariableName(TokenIterator $tokens): ?string
    {
        $tokens->tryConsumeTokenType(Lexer::TOKEN_HORIZONTAL_WS);

        return $tokens->isCurrentTokenType(Lexer::TOKEN_VARIABLE)
            ? ltrim($tokens->currentTokenValue(), '$')
            : null;
    }
}
