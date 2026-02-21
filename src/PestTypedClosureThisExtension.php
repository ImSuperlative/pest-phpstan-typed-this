<?php

namespace ImSuperlative\PestPhpstanTypedThis;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\FunctionParameterClosureThisExtension;
use PHPStan\Type\Type;

/**
 * Resolves $this type in Pest test/hook closures with per-file typed properties.
 *
 * Returns PestTestCaseType which extends ObjectType with writable dynamic properties
 * parsed from @property annotations and beforeEach assignments.
 *
 * This gives $this both TestCase methods AND typed dynamic properties
 * that are readable and writable (unlike ObjectShapeType which is read-only).
 */
final class PestTypedClosureThisExtension implements FunctionParameterClosureThisExtension
{
    /** All Pest functions that bind $this. */
    private const array PEST_FUNCTIONS = [
        'it',
        'test',
        'describe',
        'beforeEach',
        'afterEach',
        'beforeAll',
        'afterAll',
        'Pest\it',
        'Pest\test',
        'Pest\describe',
        'Pest\beforeEach',
        'Pest\afterEach',
        'Pest\beforeAll',
        'Pest\afterAll',
    ];

    /** @param  class-string  $testCaseClass */
    public function __construct(
        private PestFilePropertyParser $parser,
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

        return new PestTestCaseType($this->testCaseClass, $properties);
    }
}
