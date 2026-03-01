<?php

namespace ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns;

use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Models\Form;

trait HasTeamSetup
{
    public string $teamName;

    public Form $form;

    public function setUpTeam(): void
    {
        $this->teamName = 'Test Team';
        $this->form = new Form;
    }
}
