<?php

use ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified\ToBeBooleanRule;
use ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified\ToBeEmptyRule;
use ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified\ToBeNullRule;
use ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified\ToHaveCountZeroRule;
use ImSuperlative\PestPhpstanTypedThis\Rules\AssertionCanBeSimplified\ToHaveLengthZeroRule;
use ImSuperlative\PestPhpstanTypedThis\Tests\ConfigurableRuleTestCase;

describe('ToBeBooleanRule', function () {
    beforeEach(fn () => ConfigurableRuleTestCase::useRule(new ToBeBooleanRule));

    it('simplifies toBe/toEqual with boolean arguments', function () {
        $this->analyse([dirname(__DIR__).'/Fixtures/Rules/ToBeBooleanFixture.php'], [
            ['Assertion `->toBe(true)` can be simplified to `->toBeTrue()`.', 4],
            ['Assertion `->toBe(false)` can be simplified to `->toBeFalse()`.', 5],
            ['Assertion `->toEqual(true)` can be simplified to `->toBeTrue()`.', 6],
            ['Assertion `->toEqual(false)` can be simplified to `->toBeFalse()`.', 7],
        ]);
    });
});

describe('ToBeNullRule', function () {
    beforeEach(fn () => ConfigurableRuleTestCase::useRule(new ToBeNullRule));

    it('simplifies toBe/toEqual with null argument', function () {
        $this->analyse([dirname(__DIR__).'/Fixtures/Rules/ToBeNullFixture.php'], [
            ['Assertion `->toBe(null)` can be simplified to `->toBeNull()`.', 4],
            ['Assertion `->toEqual(null)` can be simplified to `->toBeNull()`.', 5],
        ]);
    });
});

describe('ToBeEmptyRule', function () {
    beforeEach(fn () => ConfigurableRuleTestCase::useRule(new ToBeEmptyRule));

    it('simplifies toBe/toEqual with empty array', function () {
        $this->analyse([dirname(__DIR__).'/Fixtures/Rules/ToBeEmptyFixture.php'], [
            ['Assertion `->toBe([])` can be simplified to `->toBeEmpty()`.', 4],
            ['Assertion `->toEqual([])` can be simplified to `->toBeEmpty()`.', 5],
        ]);
    });
});

describe('ToHaveCountZeroRule', function () {
    beforeEach(fn () => ConfigurableRuleTestCase::useRule(new ToHaveCountZeroRule));

    it('simplifies toHaveCount(0) to toBeEmpty()', function () {
        $this->analyse([dirname(__DIR__).'/Fixtures/Rules/ToHaveCountZeroFixture.php'], [
            ['Assertion `->toHaveCount(0)` can be simplified to `->toBeEmpty()`.', 4],
        ]);
    });
});

describe('ToHaveLengthZeroRule', function () {
    beforeEach(fn () => ConfigurableRuleTestCase::useRule(new ToHaveLengthZeroRule));

    it('simplifies toHaveLength(0) to toBeEmpty()', function () {
        $this->analyse([dirname(__DIR__).'/Fixtures/Rules/ToHaveLengthZeroFixture.php'], [
            ['Assertion `->toHaveLength(0)` can be simplified to `->toBeEmpty()`.', 4],
        ]);
    });
});
