<?php

it('can call trait methods sub inherited from parent Pest.php', function () {
    expect($this->subGreet('world'))->toBe('Sub Hello, world!');
});

it('can call trait methods inherited from parent Pest.php', function () {
    expect($this->greet('world'))->toBe('Hello, world!');
});
