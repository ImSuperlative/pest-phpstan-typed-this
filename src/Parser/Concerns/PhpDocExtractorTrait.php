<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Parser\Concerns;

use PhpParser\Node;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;

trait PhpDocExtractorTrait
{
    abstract private function getPhpDocStringResolver(): PhpDocStringResolver;

    /**
     * Iterate statements, extract doc comments, and resolve each to a PhpDocNode.
     *
     * @param  Node\Stmt[]  $stmts
     * @return array<PhpDocNode>
     */
    private function extractPhpDocNodes(array $stmts): array
    {
        $nodes = [];

        foreach ($stmts as $stmt) {
            $doc = $stmt->getDocComment();

            if ($doc === null) {
                continue;
            }

            $nodes[] = $this->getPhpDocStringResolver()->resolve($doc->getText());
        }

        return $nodes;
    }
}
