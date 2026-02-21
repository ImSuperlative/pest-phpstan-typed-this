<?php

beforeEach(function () {
    $this->object = new stdClass();
});

it('infers type from new expression', function () {
    expect($this->object)->toBeObject();
});
