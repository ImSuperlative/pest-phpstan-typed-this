<?php

use Illuminate\Support\Collection;

/**
 * @-property declares ?Collection (nullable), but assignment infers Collection (non-null).
 * When parsePhpDocProperties is disabled, inference wins and count() works directly.
 * When enabled, the nullable annotation would require a null check.
 *
 * @property ?Collection $thing
 */

beforeEach(function () {
    $this->thing = new Collection();
});

it('uses inferred type when @property parser is disabled', function () {
    expect($this->thing->count())->toBeInt();
});
