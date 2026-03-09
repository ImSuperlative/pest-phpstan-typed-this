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

## Union type narrowing

When `expect()` receives a union type, property and method access automatically narrows the union to only members that have the accessed member. If no members match, it falls back to the original type so PHPStan reports the error normally.

```php
it('narrows union types on property access', function () {
    /** @var Order|Order[]|Collection|null $order */
    $order = Order::factory()->create();

    expect($order)
        ->id->toBeInt()          // ->id narrows to Order (only Order has id)
        ->status->toBeString();  // chained access continues on Order
});

it('narrows union types on method access', function () {
    /** @var Order|Order[]|null $order */
    $order = Order::factory()->create();

    expect($order)
        ->getItems()->toHaveCount(3);  // ->getItems() narrows to Order
});
```

This works with any union type, including nullable types (`Order|null`), intersection-in-union types (`(Order&HasFactory)|Collection`), and array unions (`Order|Order[]`). The narrowing delegates to PHPStan's own `hasProperty()`/`hasMethod()` per union member, so it respects stubs, PHPDocs, and extensions like Larastan.

## Sequence closure typing

`expectationSequenceTypes: true` (disabled by default) types the callback parameters in `->sequence()` based on the iterable value type:

```php
use Illuminate\Support\Collection;

it('types sequence callbacks', function () {
    /** @var Collection<int, User> $users */
    $users = User::all();

    expect($users)->toBeInstanceOf(User::class)->sequence(
        function ($user, $key) {
            // $user is Expectation<User>, $key is Expectation<int>
            $user->name->toBeString();
        },
    );
});
```

## Scoped closure typing

`expectationScopedTypes: true` (disabled by default) types the callback parameter in `->scoped()` based on the higher-order property type:

```php
it('types scoped callbacks', function () {
    $user = new User;

    expect($user)
        ->address->toBeInstanceOf(Address::class)->scoped(function ($address) {
            // $address is Expectation<Address>
            $address->city->toBeString();
            $address->zip->toBeString();
        });
});
```

## Custom expectations

Custom expectations registered via `expect()->extend()` are also supported:

```php
it('uses custom expectations', function () {
    $collection = collect([1, 2, 3]);
    expect($collection)->toBeCollection()->toHaveCount(3);
});
```
