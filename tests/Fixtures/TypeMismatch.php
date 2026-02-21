<?php

/**
 * @property string $name
 */

beforeEach(function () {
    $this->name = 'hello';
});

it('reports error when using property as wrong type', function () {
    echo $this->name->count();
});