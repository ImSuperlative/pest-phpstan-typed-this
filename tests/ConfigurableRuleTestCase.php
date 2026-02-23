<?php

namespace ImSuperlative\PestPhpstanTypedThis\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<Rule>
 */
class ConfigurableRuleTestCase extends RuleTestCase
{
    private static Rule $rule;

    public static function useRule(Rule $rule): void
    {
        self::$rule = $rule;
    }

    protected function getRule(): Rule
    {
        return self::$rule;
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [dirname(__DIR__).'/extension.neon'];
    }
}
