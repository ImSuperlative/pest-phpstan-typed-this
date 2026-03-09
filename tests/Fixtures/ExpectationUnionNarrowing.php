<?php

use ImSuperlative\PhpstanPest\Tests\Fixtures\Models\Order;

it('narrows union types on property access', function () {
    /** @var Order|array<Order>|null $order */
    $order = null;

    // Property access should narrow Order|array<Order>|null to Order (only Order has ->id)
    expect($order)->id->toBeInt();
});

it('narrows union types on method access', function () {
    /** @var Order|array<Order>|null $order */
    $order = null;

    // Method access should narrow to Order (only Order has ->getItems())
    expect($order)->getItems()->toHaveCount(0);
});

it('preserves type when all union members have the property', function () {
    /** @var Order|Order $order */
    $order = new Order();

    // No narrowing needed — both members have ->id
    expect($order)->id->toBeInt();
});

it('chains property access after union narrowing', function () {
    /** @var Order|array<Order>|null $order */
    $order = null;

    // After ->id narrows to Order, chained ->status should also work
    expect($order)
        ->id->toBeInt()
        ->status->toBeString();
});

it('handles method call narrowing in higher-order chains', function () {
    /** @var Order|array<Order>|null $order */
    $order = null;

    // ->getItems() narrows to Order, then assertion chains
    expect($order)
        ->getItems()->toHaveCount(0)
        ->status->toBeString();
});
