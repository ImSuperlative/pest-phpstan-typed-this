<?php

it('has simplifiable boolean assertions', function () {
    expect(true)->toBe(true);    // line 4: error
    expect(false)->toBe(false);  // line 5: error
    expect(true)->toEqual(true); // line 6: error
    expect(false)->toEqual(false); // line 7: error

    // Non-matching cases — should NOT trigger
    expect(1)->toBe(1);
    expect('foo')->toBe('foo');
    expect(null)->toBe(null);
});
