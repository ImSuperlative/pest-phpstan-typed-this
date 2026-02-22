# pest-phpstan-typed-this

PHPStan extension that provides typed `$this` in Pest PHP test closures.

Solves the "undefined property" errors PHPStan reports when you assign dynamic properties to `$this` in `beforeEach` and use them in your tests.

> ⚠️ **Note:** This extension uses some [unstable PHPStan APIs](#phpstan-api-compatibility) and may require updates on minor PHPStan releases.

## Requirements

- PHP 8.2+
- PHPStan 2.0+

## Installation

```bash
composer require --dev imsuperlative/pest-phpstan-typed-this
```

If you have `phpstan/extension-installer`, the extension is registered automatically. Otherwise, add it to your `phpstan.neon`:

```neon
includes:
    - vendor/imsuperlative/pest-phpstan-typed-this/extension.neon
```

## How it works

The extension parses your Pest test files and picks up property types from two sources (**in precedence order**):

### 1. PHPDoc tags (default: disabled)

`@pest-property` and `@property` tags at the top of your test file. `@pest-property` takes precedence over `@property`.

```php
<?php

/**
 * @pest-property User $user
 * @property ?Team $team
 */

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = null;
});
```

### 2. Auto-inference from assignments (default: enabled)

The extension infers types from `$this->prop = expr` assignments using PHPStan's own type resolver. Any expression PHPStan can resolve will work:

```php
beforeEach(function () {
    $this->user    = new User();
    $this->service = app(UserService::class);
    $this->mock    = mock(Mailer::class);
    // ... any expression PHPStan can type
});
```

PHPDoc tags take precedence over inferred types.

## Configuration

```neon
parameters:
    pestPhpstanTypedThis:
        testCaseClass: PHPUnit\Framework\TestCase  # Base test case class
        parsePestPropertyTags: false               # @pest-property tags
        parsePhpDocProperties: false               # @property PHPDoc tags
        parseAssignments: true                     # $this->prop = expr inference
```

By default, the extension uses assignment inference. The `@pest-property` and `@property` PHPDoc parsers are opt-in.

## What it solves

### Typed dynamic properties

```php
<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('has a name', function () {
    // PHPStan knows $this->user is User
    expect($this->user->name)->toBeString();
});
```

### Protected TestCase methods

Protected methods like `mock()`, `assertDatabaseHas()`, etc. are accessible in Pest closures:

```php
<?php

use App\Services\PaymentService;

beforeEach(function () {
    $this->mock(PaymentService::class)->shouldIgnoreMissing();
});

it('asserts database state', function () {
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseMissing('users', ['email' => 'ghost@example.com']);
});
```

### Higher-order expectation proxying

Pest proxies method calls on `expect()` to the underlying value via `__call`. This extension tells PHPStan these are valid:

```php
<?php

it('proxies collection methods', function () {
    $items = collect([1, 2, 3]);

    expect($items)->first()->toBe(1);
    expect($items)->pluck('name')->toHaveCount(3);
    expect($items)->where('active', true)->toHaveCount(1);
    expect($items)->every(fn (int $item) => $item > 0)->toBeTrue();
});
```

### Custom expectations

Custom expectations registered via `expect()->extend()` are also supported:

```php
<?php

it('uses custom expectations', function () {
    $collection = collect([1, 2, 3]);
    expect($collection)->toBeCollection()->toHaveCount(3);
});
```

## Supported Pest functions

`it`, `test`, `describe`, `beforeEach`, `afterEach` (both short and `Pest\` namespaced forms).

## PHPStan API Compatibility

This extension relies on 4 PHPStan APIs that are not covered by the backward compatibility promise. These are baselined and may require updates on minor PHPStan releases.

| API | Used by | Why |
|---|---|---|
| `WrappedExtendedPropertyReflection` | All sources | Wraps `PropertyReflection` into `ExtendedPropertyReflection` — required by `UnresolvedPropertyPrototypeReflection` return types |
| `UnresolvedPropertyPrototypeReflection` | All sources | Required by `ObjectType::getUnresolvedPropertyPrototype()` — the only way to hook into property access on a custom type |
| `PhpDocStringResolver::resolve()` | `@pest-property` | Parses PHPDoc blocks to extract `@pest-property` tags from file-level statements |
| `PhpDocStringResolver::resolve()` | `@property` | Parses PHPDoc blocks to extract `@property` tags from file-level statements |

The assignment inference parser uses stable APIs (`NodeFinder`, `InitializerExprTypeResolver`) for **discovery**, but all sources depend on the unstable property exposure APIs above.

## License

MIT
