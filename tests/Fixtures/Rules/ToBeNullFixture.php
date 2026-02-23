<?php

it('has simplifiable null assertions', function () {
    expect(null)->toBe(null);    // line 4: error
    expect(null)->toEqual(null); // line 5: error

    // Non-matching cases — should NOT trigger
    expect(true)->toBe(true);
    expect(0)->toBe(0);
});
