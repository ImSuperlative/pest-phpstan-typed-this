# Expectations

## Higher-order expectation proxying

Pest proxies method calls on `expect()` to the underlying value via `__call`. This extension tells PHPStan these are valid:

```php
it('proxies collection methods', function () {
    $items = collect([1, 2, 3]);

    expect($items)->first()->toBe(1);
    expect($items)->pluck('name')->toHaveCount(3);
    expect($items)->where('active', true)->toHaveCount(1);
    expect($items)->every(fn (int $item) => $item > 0)->toBeTrue();
});
```

## Higher-order property access

`expectationPropertyAccess: true` (default) allows accessing properties directly on `expect()`:

```php
use App\Models\Participant;

it('asserts on object properties', function () {
    $participant = Participant::factory()->create();

    expect($participant)
        ->name->toBe('Test');
});
```

With `expectationPropertyAccess` disabled, narrow the type first with `toBeInstanceOf()`, `toBeArray()`, or `toBeObject()`:

```php
expect($participant)->toBeInstanceOf(Participant::class)
    ->name->toBe('Test');

expect(['a' => 1])->toBeArray()
    ->toHaveKey('a');
```

## Custom expectations

Custom expectations registered via `expect()->extend()` are also supported:

```php
it('uses custom expectations', function () {
    $collection = collect([1, 2, 3]);
    expect($collection)->toBeCollection()->toHaveCount(3);
});
```