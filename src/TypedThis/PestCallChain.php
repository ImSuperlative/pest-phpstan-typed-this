<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;

/**
 * Value object representing an unwrapped pest()/uses() method chain.
 *
 * Encapsulates chain parsing, scope extraction, and argument collection
 * for Pest's uses(Foo::class)->in('path') and pest()->extend(Foo::class) patterns.
 */
final readonly class PestCallChain
{
    private const array PEST_FUNCTIONS = ['pest', 'Pest\\pest'];

    private const array USES_FUNCTIONS = ['uses', 'Pest\\uses'];

    private const array EXTEND_METHODS = ['extend', 'extends', 'use', 'uses'];

    private const string IN_METHOD = 'in';

    /** @param list<MethodCall> $methods */
    private function __construct(
        private Expr $root,
        private array $methods,
    ) {}

    /**
     * Try to parse an expression as a Pest call chain.
     * Returns null if the root is not a pest() or uses() call.
     */
    public static function tryFrom(Expr $expr): ?self
    {
        $methods = [];
        $current = $expr;

        while ($current instanceof MethodCall) {
            $methods[] = $current;
            $current = $current->var;
        }

        $chain = new self($current, $methods);

        return $chain->isValid() ? $chain : null;
    }

    /**
     * Extract the ->in() scope path, if present.
     */
    public function scope(): ?string
    {
        $inCall = $this->findInCall();

        if (! $inCall instanceof MethodCall) {
            return null;
        }

        $firstArg = $inCall->getArgs()[0] ?? null;

        return $firstArg !== null && $firstArg->value instanceof String_
            ? $firstArg->value->value
            : null;
    }

    /**
     * Collect all class arguments from extend methods and root uses() call.
     *
     * @return list<Arg>
     */
    public function classArgs(): array
    {
        return [...$this->extendArgs(), ...$this->rootArgs()];
    }

    private function isValid(): bool
    {
        return $this->isPestCall() || $this->isUsesCall();
    }

    private function isPestCall(): bool
    {
        return $this->root instanceof FuncCall
            && $this->root->name instanceof Name
            && in_array($this->root->name->toString(), self::PEST_FUNCTIONS, true);
    }

    private function isUsesCall(): bool
    {
        return $this->root instanceof FuncCall
            && $this->root->name instanceof Name
            && in_array($this->root->name->toString(), self::USES_FUNCTIONS, true);
    }

    /** @return list<Arg> */
    private function extendArgs(): array
    {
        /** @var list<Arg> */
        return array_reduce(
            $this->methods,
            fn (array $carry, MethodCall $call) => $this->isExtendMethod($call)
                ? [...$carry, ...$call->getArgs()]
                : $carry,
            [],
        );
    }

    /** @return list<Arg> */
    private function rootArgs(): array
    {
        return $this->root instanceof FuncCall && $this->isUsesCall()
            ? array_values($this->root->getArgs())
            : [];
    }

    private function findInCall(): ?MethodCall
    {
        return array_find(
            $this->methods,
            fn (MethodCall $call) => $call->name instanceof Identifier && $call->name->name === self::IN_METHOD,
        );
    }

    private function isExtendMethod(MethodCall $call): bool
    {
        return $call->name instanceof Identifier
            && in_array($call->name->name, self::EXTEND_METHODS, true);
    }
}
