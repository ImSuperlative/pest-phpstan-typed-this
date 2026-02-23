# Uses & trait resolution

Resolves trait methods from `uses()` and `pest()->extend()` calls so PHPStan recognises methods added via traits.

## Configuration

| Option            | Default | Description                                      |
|-------------------|---------|--------------------------------------------------|
| `parseUses`       | `true`  | Resolve traits from `uses()`/`pest()->extend()` in the file |
| `parseParentUses` | `true`  | Walk up directories for parent `Pest.php` files  |

## Supported forms

All of these are recognised:

```php
uses(HasFactory::class);
pest()->extend(HasFactory::class);
pest()->extends(HasFactory::class);
pest()->use(HasFactory::class);
pest()->uses(HasFactory::class);
pest()->group('api')->extend(HasFactory::class);
```

## Parent Pest.php resolution

When `parseParentUses: true`, the extension walks up from the test file looking for `Pest.php` files and applies traits whose `->in()` scope matches the file's path.

```
tests/
  Pest.php              # pest()->extends(HasGreeting::class)->in('Inherited')
  Inherited/
    FooTest.php          # $this->greet() works - matches ->in('Inherited')
    Sub/
      BarTest.php        # $this->greet() also works - nested under 'Inherited'
```

The `->in()` path is relative to the `Pest.php` file's directory. Files in subdirectories also match (e.g. `Inherited/Sub/Bar.php` matches `->in('Inherited')`).

Multiple scopes compose:

```php
// tests/Pest.php
pest()->extends(HasGreeting::class)->in('Inherited');
pest()->extends(HasSubGreeting::class)->in('Inherited/Sub');
```

A file at `tests/Inherited/Sub/BarTest.php` gets both `HasGreeting` and `HasSubGreeting`.

## IDE compatibility

PHPStorm copies files to a temp directory for analysis. The extension detects this and resolves paths back to the project root using `%currentWorkingDirectory%`, so parent `Pest.php` discovery works in both CLI and IDE.