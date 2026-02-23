<?php

it('has simplifiable toHaveLength assertions', function () {
    expect('')->toHaveLength(0); // line 4: error

    // Non-matching cases — should NOT trigger
    expect('hello')->toHaveLength(5);
    expect('a')->toHaveLength(1);
});
