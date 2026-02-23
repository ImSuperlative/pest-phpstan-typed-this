<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Concerns;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;

trait CanParseStatements
{
    abstract private function getParser(): Parser;

    /** @return Node\Stmt[]|null */
    private function parseStatements(string $filePath): ?array
    {
        $content = file_get_contents($filePath);
        $stmts = $content !== false ? $this->getParser()->parse($content) : null;

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
}
