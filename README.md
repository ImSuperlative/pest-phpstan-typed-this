# pest-phpstan-typed-this

PHPStan extension that provides typed `$this` in Pest PHP test closures.

Solves the "undefined property" errors PHPStan reports when you assign dynamic properties to `$this` in `beforeEach` and use them in your tests.

## Requirements

- PHP 8.2+
- PHPStan 2.0+

## Installation

```bash
composer require --dev local/pest-phpstan-typed-this
```

If you have `phpstan/extension-installer`, the extension is registered automatically. Otherwise, add it to your `phpstan.neon`:

```neon
includes:
    - vendor/local/pest-phpstan-typed-this/extension.neon
```

## How it works

The extension parses your Pest test files and picks up property types from two sources:

### 1. `@property` annotations

Add `@property` PHPDoc tags at the top of your test file:

```php
<?php

/**
 * @property User $user
 * @property ?Team $team
 */

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->team = null;
});

it('has a user', function () {
    // PHPStan knows $this->user is User
    expect($this->user->email)->toBeString();
});
```

### 2. Auto-inference from assignments

The extension infers types from `$this->prop = expr` assignments using PHPStan's own type resolver. Any expression PHPStan can resolve will work:

```php
beforeEach(function () {
    $this->user    = new User();
    $this->service = app(UserService::class);
    $this->mock    = mock(Mailer::class);
    // ... any expression PHPStan can type
});
```

`@property` annotations take precedence over inferred types.

## Configuration

By default the extension uses `PHPUnit\Framework\TestCase` as the base class. To use a custom test case:

```neon
parameters:
    pestPhpstanTypedThis:
        testCaseClass: Tests\TestCase
```

## What it solves

### Typed dynamic properties

```php
<?php

use App\Models\User;

/**
 * @property User $user
 */

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

`it`, `test`, `describe`, `beforeEach`, `afterEach`, `beforeAll`, `afterAll` (both short and `Pest\` namespaced forms).

## License

MIT