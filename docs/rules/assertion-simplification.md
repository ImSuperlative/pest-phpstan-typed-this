# Assertion simplification rules

PHPStan rules that suggest simpler Pest assertion methods when a more expressive alternative exists.

All rules are enabled by default.

## Rules

### `toBe(true)` / `toBe(false)` → `toBeTrue()` / `toBeFalse()`

```php
// Before
expect($value)->toBe(true);
expect($value)->toEqual(false);

// After
expect($value)->toBeTrue();
expect($value)->toBeFalse();
```

**Identifier:** `pest.toBeTrue`, `pest.toBeFalse`

### `toBe(null)` → `toBeNull()`

```php
// Before
expect($value)->toBe(null);
expect($value)->toEqual(null);

// After
expect($value)->toBeNull();
```

**Identifier:** `pest.toBeNull`

### `toBe('')` / `toBe([])` → `toBeEmpty()`

```php
// Before
expect($string)->toBe('');
expect($array)->toEqual([]);

// After
expect($string)->toBeEmpty();
expect($array)->toBeEmpty();
```

**Identifier:** `pest.toBeEmpty`

### `toHaveCount(0)` → `toBeEmpty()`

```php
// Before
expect($collection)->toHaveCount(0);

// After
expect($collection)->toBeEmpty();
```

**Identifier:** `pest.toBeEmpty`

### `toHaveLength(0)` → `toBeEmpty()`

```php
// Before
expect($string)->toHaveLength(0);

// After
expect($string)->toBeEmpty();
```

**Identifier:** `pest.toBeEmpty`

## Ignoring rules

Use PHPStan's `ignoreErrors` with the rule identifiers:

```neon
parameters:
    ignoreErrors:
        - identifier: pest.toBeNull
        - identifier: pest.toBeEmpty
```