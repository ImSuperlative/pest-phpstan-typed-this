# Typed dynamic properties

Resolves `$this->prop` types inside Pest test closures so PHPStan stops reporting "undefined property" errors.

## Configuration

Three strategies are available, controlled by config flags:

| Option                 | Default | Description                          |
|------------------------|---------|--------------------------------------|
| `parseAssignments`     | `true`  | Infer types from `$this->prop = expr`|
| `parsePestPropertyTags`| `false` | Parse `@pest-property` PHPDoc tags   |
| `parsePhpDocProperties`| `false` | Parse `@property` PHPDoc tags        |

Precedence: `@pest-property` > `@property` > assignment inference.

## Assignment inference

`parseAssignments: true` (default) uses PHPStan's own type resolver to infer types from assignments in `beforeEach`:

```php
<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(UserService::class);
    $this->mock = mock(Mailer::class);
});

it('has a name', function () {
    // PHPStan knows $this->user is User
    expect($this->user->name)->toBeString();
});
```

## PHPDoc tags

Enable `parsePestPropertyTags` or `parsePhpDocProperties` to declare types via PHPDoc at the top of your test file:

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

PHPDoc tags take precedence over inferred types when both are present.