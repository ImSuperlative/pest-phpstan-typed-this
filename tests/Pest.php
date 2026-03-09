<?php

use ImSuperlative\PhpstanPest\Tests\ConfigurableRuleTestCase;
use ImSuperlative\PhpstanPest\Tests\Fixtures\Concerns\HasGreeting;
use ImSuperlative\PhpstanPest\Tests\Fixtures\Concerns\HasSubGreeting;
use ImSuperlative\PhpstanPest\Tests\TestCase;
use ImSuperlative\PhpstanPest\Tests\TypeInferenceTestCase;

pest()->extend(TypeInferenceTestCase::class)
    ->in('Unit');

pest()->extend(ConfigurableRuleTestCase::class)
    ->in('Rules');

pest()->extends(TestCase::class, HasGreeting::class)
    ->in('Inherited');

pest()->extends(HasSubGreeting::class)
    ->group('inherited-sub')
    ->in('Inherited/Sub');
