<?php

it('has simplifiable toHaveCount assertions', function () {
    expect([])->toHaveCount(0); // line 4: error

    // Non-matching cases — should NOT trigger
    expect([])->toHaveCount(1);
    expect([])->toHaveCount(5);
});
