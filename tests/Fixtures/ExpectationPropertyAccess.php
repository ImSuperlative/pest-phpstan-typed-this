<?php

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Models\Attendee;

it('allows higher-order property access on expectations', function () {
    $attendee = new Attendee();

    expect($attendee)->email->toBeString();
    expect($attendee)->age->toBeInt();

    // Chained property access on separate expect calls
    expect($attendee)->email->toBeString()->toBe('test@example.com');
    expect($attendee)->age->toBeInt()->toBe(25);
});
