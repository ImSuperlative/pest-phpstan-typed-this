<?php

use ImSuperlative\PestPhpstanTypedThis\Tests\ConfigurableRuleTestCase;
use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns\HasGreeting;
use ImSuperlative\PestPhpstanTypedThis\Tests\Fixtures\Concerns\HasSubGreeting;
use ImSuperlative\PestPhpstanTypedThis\Tests\TestCase;
use ImSuperlative\PestPhpstanTypedThis\Tests\TypeInferenceTestCase;

pest()->extend(TypeInferenceTestCase::class)
    ->in('Unit');

pest()->extend(ConfigurableRuleTestCase::class)
    ->in('Rules');

pest()->extends(TestCase::class, HasGreeting::class)
    ->in('Inherited');

pest()->extends(HasSubGreeting::class)
    ->group('inherited-sub')
    ->in('Inherited/Sub');
