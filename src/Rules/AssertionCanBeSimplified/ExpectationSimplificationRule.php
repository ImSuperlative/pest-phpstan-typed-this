<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Pest\Expectation;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<MethodCall>
 */
abstract class ExpectationSimplificationRule implements Rule
{
    /** @return list<string> */
    abstract protected function getMethodNames(): array;

    abstract protected function matchesArgument(Node $arg): bool;

    abstract protected function describeArgument(Node $arg): string;

    abstract protected function getReplacement(Node $arg): string;

    abstract protected function getIdentifier(Node $arg): string;

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param  MethodCall  $node
     * @return list<IdentifierRuleError>
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->shouldReport($node, $scope)) {
            return [];
        }

        return [$this->buildError($node, $node->getArgs()[0]->value)];
    }

    public function shouldReport(MethodCall $node, Scope $scope): bool
    {
        return $this->isTargetMethodCall($node)
            && $this->hasSingleArgument($node)
            && $this->matchesArgument($node->getArgs()[0]->value)
            && $this->isOnExpectation($node, $scope);
    }

    public function isTargetMethodCall(MethodCall $node): bool
    {
        return $node->name instanceof Identifier
            && in_array($node->name->name, $this->getMethodNames(), true);
    }

    public function hasSingleArgument(MethodCall $node): bool
    {
        return count($node->getArgs()) === 1;
    }

    public function isOnExpectation(MethodCall $node, Scope $scope): bool
    {
        return in_array(
            Expectation::class,
            $scope->getType($node->var)->getObjectClassNames(),
            true,
        );
    }

    /**
     * @throws ShouldNotHappenException
     */
    public function buildError(MethodCall $node, Node $arg): IdentifierRuleError
    {
        $replacement = $this->getReplacement($arg);
        /** @var Identifier $name */
        $name = $node->name;
        $original = $name->name . '(' . $this->describeArgument($arg) . ')';

        /** @var IdentifierRuleError */
        return RuleErrorBuilder::message(
            "Assertion `->$original` can be simplified to `->$replacement()`.",
        )
            ->identifier($this->getIdentifier($arg))
            ->fixNode($node, static function (MethodCall $node) use ($replacement): MethodCall {
                $new = clone $node;
                $new->name = new Identifier($replacement);
                $new->args = [];

                return $new;
            })
            ->build();
    }
}
