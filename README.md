# phpstan-pest

PHPStan extension that provides typed `$this` in Pest PHP test closures.

Solves the "undefined property" errors PHPStan reports when you assign dynamic properties to `$this` in `beforeEach` and use them in your tests.

> **Note:** This extension uses some [unstable PHPStan APIs](#phpstan-api-compatibility) and may require updates on minor PHPStan releases.

## Requirements

- PHP 8.4+
- PHPStan 2.0+

## Installation

```bash
composer require --dev imsuperlative/phpstan-pest
```

If you have `phpstan/extension-installer`, the extension is registered automatically. Otherwise, add it to your `phpstan.neon`:

```neon
includes:
    - vendor/imsuperlative/phpstan-pest/extension.neon
```

## Configuration

```neon
parameters:
    PhpstanPest:
        testCaseClass: PHPUnit\Framework\TestCase  # Base test case class
        parseAssignments: true                     # $this->prop = expr inference
        parsePestPropertyTags: false               # @pest-property tags
        parsePhpDocProperties: false               # @property PHPDoc tags
        parseUses: true                            # Resolve uses()/pest()->extend() traits
        parseParentUses: true                      # Walk up for parent Pest.php files
        expectationPropertyAccess: true            # Higher-order property access on expect()
        expectationSequenceTypes: false            # Typed closures in ->sequence()
        expectationScopedTypes: false              # Typed closures in ->scoped()
        simplifyToBe: true                         # ->toBe(true/false) → ->toBeTrue/False()
        simplifyToBeNull: true                     # ->toBe(null) → ->toBeNull()
        simplifyToBeEmpty: true                    # ->toBe('')/->toBe([]) → ->toBeEmpty()
        simplifyToHaveCount: true                  # ->toHaveCount(0) → ->toBeEmpty()
        simplifyToHaveLength: true                 # ->toHaveLength(0) → ->toBeEmpty()
```

If your project extends the base TestCase (e.g. in Laravel with Testbench), point `testCaseClass` to your own class:

```neon
parameters:
    PhpstanPest:
        testCaseClass: Tests\TestCase
```

## Features

### Typed dynamic properties

Infers types from `$this->prop = expr` assignments in `beforeEach` using PHPStan's own type resolver:

```php
beforeEach(function () {
    $this->user = User::factory()->create();
});

it('has a name', function () {
    // PHPStan knows $this->user is User
    expect($this->user->name)->toBeString();
});
```

Alternatively, declare types via `@pest-property` or `@property` PHPDoc tags.

[Full documentation](docs/features/typed-dynamic-properties.md)

### Uses & trait resolution

Resolves trait methods from `uses()` and `pest()->extend()` calls, including parent `Pest.php` files with `->in()` scoping:

```php
// tests/Pest.php
pest()->extends(HasGreeting::class)->in('Inherited');

// tests/Inherited/FooTest.php
it('can call trait methods', function () {
    expect($this->greet('world'))->toBe('Hello, world!');
});
```

[Full documentation](docs/features/uses-trait-resolution.md)

### Expectations

Higher-order method proxying, property access on `expect()`, union type narrowing, and custom expectations:

```php
it('proxies and accesses properties', function () {
    expect($user)
        ->name->toBe('Test');

    expect(collect([1, 2, 3]))->first()->toBe(1);
});

// Union types are automatically narrowed to members that have the accessed property/method
it('narrows union types', function () {
    /** @var Order|Order[]|null $order */
    $order = Order::factory()->create();

    expect($order)
        ->id->toBeInt()          // narrows to Order (only Order has ->id)
        ->status->toBeString();
});
```

[Full documentation](docs/features/expectations.md)

## Rules

### Assertion simplification

Suggests simpler Pest assertion methods when a more expressive alternative exists:

| Before | After |
|---|---|
| `->toBe(true)` | `->toBeTrue()` |
| `->toBe(false)` | `->toBeFalse()` |
| `->toBe(null)` | `->toBeNull()` |
| `->toBe('')` / `->toBe([])` | `->toBeEmpty()` |
| `->toHaveCount(0)` | `->toBeEmpty()` |
| `->toHaveLength(0)` | `->toBeEmpty()` |

[Full documentation](docs/rules/assertion-simplification.md)

## Supported Pest functions

`it`, `test`, `describe`, `beforeEach`, `afterEach` (both short and `Pest\` namespaced forms).

## PHPStan API Compatibility

This extension relies on PHPStan APIs not covered by the backward compatibility promise. These are baselined and may require updates on minor PHPStan releases.

| API | Why |
|---|---|
| `WrappedExtendedPropertyReflection` | Wraps `PropertyReflection` into `ExtendedPropertyReflection` for property access on custom types |
| `UnresolvedPropertyPrototypeReflection` | Required by `ObjectType::getUnresolvedPropertyPrototype()` |
| `UnresolvedMethodPrototypeReflection` | Required for trait method resolution on custom types |
| `ExtendedMethodReflection` | Required for exposing trait methods as public |
| `PhpDocStringResolver::resolve()` | Parses PHPDoc blocks for `@pest-property` and `@property` tags |

## License

MIT
