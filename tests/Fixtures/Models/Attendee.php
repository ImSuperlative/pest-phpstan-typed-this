<?php

declare(strict_types=1);

namespace ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Models;

class Attendee
{
    public string $email = 'test@example.com';
    public int $age = 25;
    public string $name = 'John';
    public Form $form;

    public function __construct()
    {
        $this->form = new Form;
    }
}
