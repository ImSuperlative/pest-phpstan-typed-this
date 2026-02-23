<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\TypedThis;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
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
        'beforeAll',
        'afterEach',
        'afterAll',
        'Pest\it',
        'Pest\test',
        'Pest\describe',
        'Pest\beforeEach',
        'Pest\beforeAll',
        'Pest\afterEach',
        'Pest\afterAll',
    ];

    /** @param  class-string  $testCaseClass */
    public function __construct(
        private FilePropertyParser $parser,
        private ReflectionProvider $reflectionProvider,
        private string $testCaseClass = 'PHPUnit\Framework\TestCase',
        private bool $parseUses = true,
        private bool $parseParentUses = true,
    ) {}

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
        $filePath = $scope->getFile();

        return new TestCaseType(
            $this->testCaseClass,
            $this->parser->parse($filePath),
            $this->reflectionProvider,
            $this->resolveTraits($filePath),
        );
    }

    /**
     * @return list<ClassReflection>
     */
    private function resolveTraits(string $filePath): array
    {
        if (! $this->parseUses && ! $this->parseParentUses) {
            return [];
        }

        $classes = $this->parseParentUses
            ? $this->parser->parseUsesWithParents($filePath)
            : $this->parser->parseUses($filePath);

        return array_values(array_filter(
            array_map(fn (string $class) => $this->reflectIfTrait($class), $classes),
        ));
    }

    private function reflectIfTrait(string $className): ?ClassReflection
    {
        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $reflection = $this->reflectionProvider->getClass($className);

        return $reflection->isTrait() ? $reflection : null;
    }
}
