<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

use ImSuperlative\PestPhpstanTypedThis\Concerns\CanParseStatements;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use PhpParser\Parser;

/**
 * Parses Pest's uses()/pest()->extend() calls from a file's AST.
 *
 * Supported forms:
 *   - uses(Foo::class)
 *   - pest()->extend(Foo::class)
 *   - pest()->extends(Foo::class)
 *   - pest()->use(Foo::class)
 *   - pest()->uses(Foo::class)
 *   - pest()->group('foo')->extend(Foo::class)
 */
final class UsesParser
{
    use CanParseStatements;

    /** @var array<string, list<class-string>> */
    private array $cache = [];

    /** @var array<string, ScopedUsesMap> */
    private array $scopedCache = [];

    /** @var array<string, list<class-string>> */
    private array $withParentsCache = [];

    public function __construct(
        private readonly NodeFinder $nodeFinder,
        private readonly Parser $parser,
        private readonly PestFileLocator $fileLocator,
    ) {}

    private function getParser(): Parser
    {
        return $this->parser;
    }

    /**
     * @param  Node\Stmt[]  $stmts
     * @return list<class-string>
     */
    public function parse(string $filePath, array $stmts): array
    {
        return $this->cache[$filePath] ??= $this->extractClassNames(
            $this->collectArgs($stmts),
        );
    }

    /**
     * Parse uses/extend calls and group class names by their ->in() scope.
     *
     * @param  Node\Stmt[]  $stmts
     */
    public function parseScopedUses(string $filePath, array $stmts): ScopedUsesMap
    {
        return $this->scopedCache[$filePath] ??= $this->collectScopedClasses($stmts);
    }

    /**
     * Parse uses from a file itself, then walk up parent directories looking for
     * Pest.php files whose ->in() scopes match this file's relative path.
     *
     * @return list<class-string>
     */
    public function parseWithParents(string $filePath): array
    {
        return $this->withParentsCache[$filePath] ??= $this->resolveWithParents($filePath);
    }

    /** @return list<class-string> */
    private function resolveWithParents(string $filePath): array
    {
        $realPath = $this->fileLocator->resolveRealPath($filePath);

        return array_reduce(
            $this->fileLocator->findParentPestFiles($filePath),
            fn (array $carry, string $pestFile) => $this->parseScopedFile($pestFile)->mergeInto(
                $carry,
                substr($realPath, strlen(dirname($pestFile)) + 1),
            ),
            $this->parseFile($filePath),
        );
    }

    /** @return list<class-string> */
    private function parseFile(string $filePath): array
    {
        $stmts = $this->parseStatements($filePath);

        return $stmts !== null ? $this->parse($filePath, $stmts) : [];
    }

    private function parseScopedFile(string $filePath): ScopedUsesMap
    {
        $stmts = $this->parseStatements($filePath);

        return $stmts !== null ? $this->parseScopedUses($filePath, $stmts) : new ScopedUsesMap;
    }

    /**
     * @param  Stmt[]  $stmts
     */
    private function collectScopedClasses(array $stmts): ScopedUsesMap
    {
        return array_reduce($stmts, function (ScopedUsesMap $map, Stmt $stmt): ScopedUsesMap {
            $chain = $this->parseStatementChain($stmt);

            return $chain !== null
                ? $map->withScope($chain[0], $chain[1])
                : $map;
        }, new ScopedUsesMap);
    }

    /**
     * @return array{string|null, list<class-string>}|null
     */
    private function parseStatementChain(Stmt $stmt): ?array
    {
        return $stmt instanceof Expression
            ? $this->parseChain($stmt->expr)
            : null;
    }

    /**
     * @return array{string|null, list<class-string>}|null
     */
    private function parseChain(Expr $expr): ?array
    {
        $chain = PestCallChain::tryFrom($expr);
        if (! $chain instanceof PestCallChain) {
            return null;
        }

        $classes = $this->extractClassNames($chain->classArgs());

        return $classes !== []
            ? [$chain->scope(), $classes]
            : null;
    }

    /**
     * @param  Node\Stmt[]  $stmts
     * @return list<Arg>
     */
    private function collectArgs(array $stmts): array
    {
        /** @var list<FuncCall|MethodCall> $nodes */
        $nodes = $this->nodeFinder->find(
            $stmts,
            fn (Node $node) => $this->isUsesCall($node) || $this->isPestExtendCall($node),
        );

        /** @var list<Arg> */
        return array_merge(...array_map(
            static fn (FuncCall|MethodCall $node) => $node->getArgs(),
            $nodes,
        ));
    }

    private function isUsesCall(Node $node): bool
    {
        return $node instanceof FuncCall
            && $node->name instanceof Name
            && in_array($node->name->toString(), ['uses', 'Pest\\uses'], true);
    }

    private function isPestExtendCall(Node $node): bool
    {
        return $node instanceof MethodCall
            && $node->name instanceof Identifier
            && in_array($node->name->name, ['extend', 'extends', 'use', 'uses'], true)
            && $this->originatesFromPest($node->var);
    }

    private function originatesFromPest(Node $node): bool
    {
        return $node instanceof FuncCall && $node->name instanceof Name
            ? in_array($node->name->toString(), ['pest', 'Pest\\pest'], true)
            : $node instanceof MethodCall && $this->originatesFromPest($node->var);
    }

    /**
     * @param  list<Arg>  $args
     * @return list<class-string>
     */
    private function extractClassNames(array $args): array
    {
        /** @var list<class-string> */
        return array_reduce($args, function (array $classes, Arg $arg): array {
            $className = $this->extractClassName($arg);

            return $className !== null ? [...$classes, $className] : $classes;
        }, []);
    }

    /**
     * @return class-string|null
     */
    private function extractClassName(Arg $arg): ?string
    {
        if (! $this->isClassConstFetch($arg)) {
            return null;
        }

        /** @var ClassConstFetch&object{class: Name} $fetch */
        $fetch = $arg->value;

        /** @var class-string */
        return $fetch->class->toString();
    }

    private function isClassConstFetch(Arg $arg): bool
    {
        return $arg->value instanceof ClassConstFetch
            && $arg->value->name instanceof Identifier
            && $arg->value->name->name === 'class'
            && $arg->value->class instanceof Name;
    }
}