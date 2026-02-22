<?php

use Illuminate\Support\Collection;

it('allows dynamic methods on Pest Expectation', function () {
    $collection = new Collection([1, 2, 3]);

    // Higher-order expectation proxy: calls count() on the Collection
    expect($collection)->count()->toBeInt();
});
