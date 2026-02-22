<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\FunctionParameterClosureThisExtension;
use PHPStan\Type\Type;

/**
 * Resolves $this type in Pest test/hook closures with per-file typed properties.
 *
 * Returns TestCaseType which extends ObjectType with writable dynamic properties
 * parsed from @-pest-property tags, @-property annotations, and beforeEach assignments.
 *
 * This gives $this both TestCase methods AND typed dynamic properties
 * that are readable and writable (unlike ObjectShapeType which is read-only).
 */
final class ClosureThisExtension implements FunctionParameterClosureThisExtension
{
    /** Pest functions that bind $this to the test case instance. */
    private const array PEST_FUNCTIONS = [
        'it',
        'test',
        'describe',
        'beforeEach',
        'afterEach',
        'Pest\it',
        'Pest\test',
        'Pest\describe',
        'Pest\beforeEach',
        'Pest\afterEach',
    ];

    /** @param  class-string  $testCaseClass */
    public function __construct(
        private FilePropertyParser $parser,
        private ReflectionProvider $reflectionProvider,
        private string $testCaseClass = 'PHPUnit\Framework\TestCase',
    ) {
    }

    public function isFunctionSupported(FunctionReflection $functionReflection, ParameterReflection $parameter): bool
    {
        return in_array($functionReflection->getName(), self::PEST_FUNCTIONS, true);
    }

    public function getClosureThisTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        ParameterReflection $parameter,
        Scope $scope
    ): Type {
        $properties = $this->parser->parse($scope->getFile());

        return new TestCaseType($this->testCaseClass, $properties, $this->reflectionProvider);
    }
}