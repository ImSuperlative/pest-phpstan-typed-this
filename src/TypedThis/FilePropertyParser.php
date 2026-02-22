<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

use ImSuperlative\PestPhpstanTypedThis\Parser\Contracts\PropertyParserStrategy;
use PhpParser\Node;
use PhpParser\Node\UseItem;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PHPStan\Type\Type;

final class FilePropertyParser
{
    /** @var array<string, array<string, Type>> */
    private array $cache = [];

    /** @param PropertyParserStrategy[] $strategies */
    public function __construct(
        private readonly array $strategies,
        private readonly Parser $parser,
        private readonly NodeFinder $nodeFinder,
    ) {
    }

    /** @return array<string, Type> property name => type */
    public function parse(string $filePath): array
    {
        return $this->cache[$filePath] ??= $this->parseFile($filePath);
    }

    /** @return array<string, Type> */
    private function parseFile(string $filePath): array
    {
        $stmts = $this->parseStatements($filePath);

        if ($stmts === null) {
            return [];
        }

        return $this->runStrategies($stmts, $filePath);
    }

    /** @return Node\Stmt[]|null */
    private function parseStatements(string $filePath): ?array
    {
        $content = file_get_contents($filePath);
        $stmts = $content !== false ? $this->parser->parse($content) : null;

        return $stmts !== null ? $this->resolveNames($stmts) : null;
    }

    /**
     * @param  Node\Stmt[]  $stmts
     * @return Node\Stmt[]
     */
    private function resolveNames(array $stmts): array
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);

        /** @var Node\Stmt[] */
        return $traverser->traverse($stmts);
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