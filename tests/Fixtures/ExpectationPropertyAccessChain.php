<?php

/** @noinspection MultipleExpectChainableInspection */

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Models\Attendee;

it('chains property access on expectations', function () {
    $result = new Attendee();

    $test = expect($result);
    $test2 = expect($result)
        ->toBeInstanceOf(Attendee::class);

    expect($result)
        ->toBeString() // fails: Attendee is not string
        ->email
        ->toBeString()
        ->toBe('direct@example.com');
    expect($result)
        ->toBeInstanceOf(Attendee::class)
        ->toBeString() // fails: Attendee is not string
        ->email->toBeString();
    expect($result)
        ->form // fails: undefined property
        ->toBeString()
        ->toBe('direct@example.com');
    expect($result)
        ->form
        ->scoped(fn ($form) => $form->id->toBeInt());
    expect($result)
        ->form
        ->scoped(fn ($form) => $form->id->toBeInt())
        ->toBeString() // fails: undefined property
        ->toBe('direct@example.com');
    expect($result)
        ->email
        ->toBeString()
        ->toBe('direct@example.com');
    expect($result)
        ->email
        ->toBeInt() // fails: string is not int
        ->toBe('direct@example.com');
    expect($result)
        ->toBeInstanceOf(Attendee::class)
        ->email
        ->toBeString()
        ->toBe('direct@example.com');
    expect($result)
        ->email->toBeString()
        ->age->toBeInt();
});
