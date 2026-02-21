<?php

namespace ImSuperlative\PestPhpstanTypedThis;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\Reflection\InitializerExprContext;
use PHPStan\Reflection\InitializerExprTypeResolver;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;

/**
 * Parses Pest test files to extract typed property declarations.
 *
 * Supports two sources:
 * 1. File-level @property PHPDoc annotations (resolved via PHPStan's PhpDoc parser)
 * 2. Auto-inference from $this assignments (delegated to PHPStan's InitializerExprTypeResolver)
 *
 * PHPDoc annotations take precedence over inferred types.
 */
final class PestFilePropertyParser
{
    /** @var Type */
    private array $cache = [];

    public function __construct(
        private PhpDocStringResolver $phpDocStringResolver,
        private TypeNodeResolver $typeNodeResolver,
        private InitializerExprTypeResolver $initializerExprTypeResolver,
    ) {
    }

    /** @return array<string, Type> property name => type */
    public function parse(string $filePath): array
    {
        if (isset($this->cache[$filePath])) {
            return $this->cache[$filePath];
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return $this->cache[$filePath] = [];
        }

        $parser = (new ParserFactory)->createForNewestSupportedVersion();
        $stmts = $parser->parse($content);

        if ($stmts === null) {
            return $this->cache[$filePath] = [];
        }

        $useMap = self::buildUseMap($stmts);
        $properties = [];

        $this->parsePropertyAnnotations($stmts, $useMap, $properties);
        $this->inferFromAssignments($stmts, $filePath, $properties);

        return $this->cache[$filePath] = $properties;
    }

    /**
     * @param  Node\Stmt[]  $stmts
     * @return array<string, string> short name => FQCN
     */
    private static function buildUseMap(array $stmts): array
    {
        $map = [];
        $finder = new NodeFinder;

        foreach ($finder->findInstanceOf($stmts, Use_::class) as $useNode) {
            foreach ($useNode->uses as $useUse) {
                $map[strtolower($useUse->getAlias()->toString())] = $useUse->name->toString();
            }
        }

        return $map;
    }

    /**
     * Resolves @property annotations using PHPStan's PhpDoc parser and type resolver.
     *
     * @param  Node\Stmt[]  $stmts
     * @param  array<string, string>  $useMap
     * @param  array<string, Type>  $properties
     */
    private function parsePropertyAnnotations(array $stmts, array $useMap, array &$properties): void
    {
        $nameScope = new NameScope(null, $useMap);

        foreach ($stmts as $stmt) {
            $doc = $stmt->getDocComment();
            if ($doc === null) {
                continue;
            }

            $phpDocNode = $this->phpDocStringResolver->resolve($doc->getText());

            foreach ($phpDocNode->getPropertyTagValues() as $tag) {
                $name = ltrim($tag->propertyName, '$');
                $properties[$name] = $this->typeNodeResolver->resolve($tag->type, $nameScope);
            }
        }
    }

    /**
     * Walk the AST to find `$this->prop = expr` assignments and infer types via PHPStan.
     *
     * @param  Node\Stmt[]  $stmts
     * @param  array<string, Type>  $properties
     */
    private function inferFromAssignments(array $stmts, string $filePath, array &$properties): void
    {
        $finder = new NodeFinder;
        $context = InitializerExprContext::fromClass('stdClass', $filePath);

        foreach ($finder->findInstanceOf($stmts, Assign::class) as $assign) {
            /** @var Assign $assign */
            $propertyName = self::extractThisPropertyName($assign->var);
            if ($propertyName === null || isset($properties[$propertyName])) {
                continue;
            }

            $type = $this->initializerExprTypeResolver->getType($assign->expr, $context);
            if (! $type instanceof ErrorType) {
                $properties[$propertyName] = $type;
            }
        }
    }

    private static function extractThisPropertyName(Node\Expr $expr): ?string
    {
        if (! $expr instanceof PropertyFetch
            || ! $expr->var instanceof Variable
            || $expr->var->name !== 'this'
            || ! $expr->name instanceof Node\Identifier
        ) {
            return null;
        }

        return $expr->name->toString();
    }
}
