<?php
/**
 * @see       https://github.com/laminas/laminas-zendframework-bridge for the canonical source repository
 * @copyright https://github.com/laminas/laminas-zendframework-bridge/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-zendframework-bridge/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ZendFrameworkBridge;

class ConfigPostProcessor
{
    /** @var array String keys => string values */
    private $exactReplacements = [
        'zend-expressive' => 'expressive',
    ];

    /** @var Replacements */
    private $replacements;

    /** @var callable[] */
    private $rulesets;

    public function __construct()
    {
        $this->replacements = new Replacements();

        // Define the rulesets for replacements.
        // Each rulest receives the value being rewritten, and the key, if any.
        // It then returns either null (no match), or a callable (match).
        // A returned callable is then used to perform the replacement.
        $this->rulesets     = [
            // Exact values
            function ($value) {
                return is_string($value) && array_key_exists($value, $this->exactReplacements)
                    ? [$this, 'replaceExactValue']
                    : null;
            },

            // Aliases
            function ($value, $key) {
                return $key === 'aliases' && is_array($value)
                    ? [$this, 'replaceDependencyAliases']
                    : null;
            },

            // Array values
            function ($value, $key) {
                return null !== $key && is_array($value)
                    ? [$this, '__invoke']
                    : null;
            },
        ];
    }

    /**
     * @return array
     */
    public function __invoke(array $config)
    {
        $rewritten = [];

        foreach ($config as $key => $value) {
            $newKey = is_string($key) ? $this->replace($key) : $key;

            if (array_key_exists($newKey, $rewritten) && is_array($rewritten[$newKey])) {
                $rewritten[$newKey] = self::merge($rewritten[$newKey], $this->replace($value, $newKey));
                continue;
            }

            $rewritten[$newKey] = $this->replace($value, $newKey);
        }

        return $rewritten;
    }

    /**
     * Perform subsitutions as needed on an individual value.
     *
     * The $key is provided to allow fine-grained selection of rewrite rules.
     *
     * @param mixed $value
     * @param null|int|string $key
     * @return mixed
     */
    private function replace($value, $key = null)
    {
        $rewriteRule = $this->replacementRuleMatch($value, $key);
        return $rewriteRule($value);
    }

    /**
     * Merge two arrays together.
     *
     * If an integer key exists in both arrays and preserveNumericKeys is false, the value
     * from the second array will be appended to the first array. If both values are arrays, they
     * are merged together, else the value of the second array overwrites the one of the first array.
     *
     * Based on zend-stdlib Zend\Stdlib\ArrayUtils::merge
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     *
     * @param  array $a
     * @param  array $b
     * @return array
     */
    public static function merge(array $a, array $b)
    {
        foreach ($b as $key => $value) {
            if (! isset($a[$key]) && ! array_key_exists($key, $a)) {
                $a[$key] = $value;
                continue;
            }

            if (! $preserveNumericKeys && is_int($key)) {
                $a[] = $value;
                continue;
            }
            
            if (is_array($value) && is_array($a[$key])) {
                $a[$key] = static::merge($a[$key], $value);
                continue;
            }

            $a[$key] = $value;
        }

        return $a;
    }

    /**
     * @param mixed $value
     * @param null|int|string $key
     * @return callable Callable to invoke with value
     */
    private function replacementRuleMatch($value, $key = null)
    {
        foreach ($this->rulesets as $ruleset) {
            $result = $ruleset($value, $key);
            if (is_callable($result)) {
                return $result;
            }
        }
        return [$this, 'fallbackReplacement'];
    }

    /**
     * Replace a value using the translation table, if the value is a string.
     *
     * @param mixed $value
     * @return mixed
     */
    private function fallbackReplacement($value)
    {
        return is_string($value)
            ? $this->replacements->replace($value)
            : $value;
    }

    /**
     * Replace a value matched exactly.
     *
     * @param mixed $value
     * @return mixed
     */
    private function replaceExactValue($value)
    {
        return $this->exactReplacements[$value];
    }

    /**
     * Rewrite dependency aliases array
     *
     * In this case, we want to keep the alias as-is, but rewrite the target.
     *
     * @param array $value
     * @return array
     */
    private function replaceDependencyAliases(array $aliases)
    {
        foreach ($aliases as $alias => $target) {
            $aliases[$alias] = $this->replacements->replace($target);
        }
        return $aliases;
    }
}