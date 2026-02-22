<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Parser\Contracts;

use PhpParser\Node;
use PHPStan\Type\Type;

interface PropertyParserStrategy
{
    /**
     * @param  Node\Stmt[]  $stmts
     * @param  array<string, string>  $useMap
     * @param  array<string, Type>  $existingProperties  Optimization hint: properties already resolved by higher-precedence strategies.
     *                                                   Implementations may use this to skip redundant work; the orchestrator's `+=` handles final precedence.
     * @return array<string, Type>  Newly discovered properties (excluding any already in $existingProperties)
     */
    public function parse(array $stmts, array $useMap, array $existingProperties, string $filePath): array;
}
