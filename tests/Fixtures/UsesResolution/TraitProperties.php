<?php

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns\HasTeamSetup;
use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Models\Form;
use function PHPStan\Testing\assertType;

pest()->uses(HasTeamSetup::class);

beforeEach(function () {
    $this->setUpTeam();
});

it('can access trait properties via pest()->uses()', function () {
    assertType('string', $this->teamName);
    assertType(Form::class, $this->form);
});


