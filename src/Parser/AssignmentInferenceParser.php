<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Parser;

use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use ImSuperlative\PestPhpstanTypedThis\Parser\Concerns\FirstOccurrenceFilterTrait;
use ImSuperlative\PestPhpstanTypedThis\Parser\Contracts\PropertyParserStrategy;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeFinder;
use PHPStan\Reflection\InitializerExprContext;
use PHPStan\Reflection\InitializerExprTypeResolver;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;

final class AssignmentInferenceParser implements PropertyParserStrategy
{
    use FirstOccurrenceFilterTrait;

    public function __construct(
        private InitializerExprTypeResolver $initializerExprTypeResolver,
        private string $testCaseClass = 'PHPUnit\Framework\TestCase',
    ) {}

    public function parse(array $stmts, array $useMap, array $existingProperties, string $filePath): array
    {
        $assignments = $this->findThisAssignments($stmts);
        $newAssignments = array_diff_key($assignments, $existingProperties);

        return $this->resolveTypes($newAssignments, $filePath);
    }

    /**
     * @param  array<string, Expr>  $assignments
     * @return array<string, Type>
     */
    public function resolveTypes(array $assignments, string $filePath): array
    {
        $context = InitializerExprContext::fromClass($this->testCaseClass, $filePath);

        return array_filter(
            array_map(
                fn (Expr $expr): Type => $this->initializerExprTypeResolver->getType($expr, $context),
                $assignments,
            ),
            static fn (Type $type): bool => ! $type instanceof ErrorType,
        );
    }

    /**
     * Find all `$this->prop = expr` assignments, keeping only the first per property.
     *
     * @param  Node\Stmt[]  $stmts
     * @return array<string, Expr> property name => assigned expression
     */
    public function findThisAssignments(array $stmts): array
    {
        /** @var Assign[] $assigns */
        $assigns = (new NodeFinder)->findInstanceOf($stmts, Assign::class);

        return $this->indexByPropertyName($assigns);
    }

    /**
     * @param  Assign[]  $assigns
     * @return array<string, Node\Expr> property name => first assigned expression
     */
    public function indexByPropertyName(array $assigns): array
    {
        $pairs = [];

        foreach ($assigns as $assign) {
            $name = $this->extractThisPropertyName($assign->var);

            if ($name !== null) {
                $pairs[] = [$name, $assign->expr];
            }
        }

        return $this->collectFirstOccurrences($pairs);
    }

    /**
     * If $expr is `$this->propertyName`, return the property name. Otherwise null.
     */
    public function extractThisPropertyName(Expr $expr): ?string
    {
        if (! $expr instanceof PropertyFetch
            || ! $expr->var instanceof Variable
            || $expr->var->name !== 'this'
            || ! $expr->name instanceof Identifier
        ) {
            return null;
        }

        return $expr->name->toString();
    }
}
