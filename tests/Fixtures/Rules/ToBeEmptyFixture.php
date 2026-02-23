<?php

it('has simplifiable empty assertions', function () {
    expect([])->toBe([]);      // line 4: error
    expect([])->toEqual([]);   // line 5: error

    // Non-matching cases — should NOT trigger
    expect('hello')->toBe('hello');
    expect([1])->toBe([1]);
    expect('')->toBe('');
    expect('')->toEqual('');
});
