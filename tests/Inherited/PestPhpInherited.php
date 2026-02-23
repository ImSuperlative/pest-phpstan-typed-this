<?php

it('can call trait methods inherited from parent Pest.php', function () {
    expect($this->greet('world'))->toBe('Hello, world!');
});
