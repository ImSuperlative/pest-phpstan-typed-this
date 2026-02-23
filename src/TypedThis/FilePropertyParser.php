<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

use ImSuperlative\PestPhpstanTypedThis\Concerns\CanParseStatements;
use ImSuperlative\PestPhpstanTypedThis\Parser\Contracts\PropertyParserStrategy;
use PhpParser\Node;
use PhpParser\Node\UseItem;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PHPStan\Type\Type;

final class FilePropertyParser
{
    use CanParseStatements;

    /** @var array<string, array<string, Type>> */
    private array $cache = [];

    /** @param PropertyParserStrategy[] $strategies */
    public function __construct(
        private readonly array $strategies,
        private readonly Parser $parser,
        private readonly NodeFinder $nodeFinder,
        private readonly UsesParser $usesParser,
    ) {}

    private function getParser(): Parser
    {
        return $this->parser;
    }

    /** @return array<string, Type> property name => type */
    public function parse(string $filePath): array
    {
        return $this->cache[$filePath] ??= $this->parseFile($filePath);
    }

    /**
     * Parse uses/extend from the file itself only.
     *
     * @return list<class-string>
     */
    public function parseUses(string $filePath): array
    {
        $stmts = $this->parseStatements($filePath);

        return $stmts !== null ? $this->usesParser->parse($filePath, $stmts) : [];
    }

    /**
     * Parse uses from the file itself and from parent Pest.php files.
     *
     * @return list<class-string>
     */
    public function parseUsesWithParents(string $filePath): array
    {
        return $this->usesParser->parseWithParents($filePath);
    }

    /** @return array<string, Type> */
    private function parseFile(string $filePath): array
    {
        $stmts = $this->parseStatements($filePath);

        return $stmts === null
            ? []
            : $this->runStrategies($stmts, $filePath);
    }

    /**
     * @param  Node\Stmt[]  $stmts
     * @return array<string, Type>
     */
    private function runStrategies(array $stmts, string $filePath): array
    {
        $useMap = $this->buildUseMap($stmts);
        $properties = [];

        foreach ($this->strategies as $strategy) {
            $properties += $strategy->parse($stmts, $useMap, $properties, $filePath);
        }

        return $properties;
    }

    /**
     * @param  Node\Stmt[]  $stmts
     * @return array<string, string> short name => FQCN
     */
    private function buildUseMap(array $stmts): array
    {
        /** @var UseItem[] $uses */
        $uses = $this->nodeFinder->findInstanceOf($stmts, UseItem::class);

        return array_combine(
            array_map(fn (UseItem $use) => strtolower($use->getAlias()->toString()), $uses),
            array_map(fn (UseItem $use) => $use->name->toString(), $uses),
        );
    }
}
