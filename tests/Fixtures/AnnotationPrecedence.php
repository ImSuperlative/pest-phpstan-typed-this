<?php

use Illuminate\Support\Collection;

/**
 * Annotation declares $thing as ?Collection (nullable), assignment infers Collection (non-null).
 * If annotation wins, $thing is ?Collection so calling count() directly would be an error.
 * We null-check first to prove the nullable type from the annotation is in effect.
 *
 * @property ?Collection $thing
 */

beforeEach(function () {
    $this->thing = new Collection();
});

it('uses annotation type over inferred type', function () {
    if ($this->thing !== null) {
        expect($this->thing->count())->toBeInt();
    }
});
