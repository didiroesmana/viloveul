<?php

namespace Viloveul\Utility;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Utility
 */

use Viloveul\Core\Object;

/**
 * Example to use :.
 *
 * $word = "Hello World";
 * echo \Viloveul\Utility\Inflector::convert($word)->toUnderscore();
 * ##Result is "Hello_World"
 *
 * echo \Viloveul\Utility\Inflector::convert($word)->toUnderscore()->lowercase();
 * ##Result is "hello_world"
 */
class Inflector extends Object
{
    protected $word;

    protected $origin;

    /**
     * Constructor.
     *
     * @param   string word
     */
    public function __construct($word)
    {
        $this->word = $this->origin = $word;
    }

    /**
     * To String.
     *
     * @return string current word
     */
    public function __toString()
    {
        return $this->word;
    }

    /**
     * toCamelize.
     *
     * @param   string separator to convert
     *
     * @return object|string
     */
    public function toCamelize($separator = null)
    {
        $seps = array('-', '_');
        if (!is_null($separator)) {
            $seps = func_get_args();
        }
        $words = ucwords(str_replace($seps, ' ', $this->word));
        $this->word = str_replace(' ', '', $words);

        return $this;
    }

    /**
     * toSlug.
     *
     * @param   string separator to used
     *
     * @return object|string
     */
    public function toSlug($separator, $lowercase = true)
    {
        $word = (true === $lowercase) ? $this->lowercase() : $this->word;
        $this->word = preg_replace('#[^a-zA-Z0-9\-\.\:]+#', $separator, $word);

        return $this;
    }

    /**
     * toUnderscore.
     *
     * @return object|string
     */
    public function toUnderscore()
    {
        $this->word = trim(str_replace(' ', '', preg_replace('/(?:\\w)([a-z]+)/', '_\\0', $this->word)), '_');

        return $this;
    }

    /**
     * lowercase.
     *
     * @return object|string
     */
    public function lowercase()
    {
        $this->word = (defined('MB_ENABLED') && MB_ENABLED) ?
            mb_strtolower($this->word) :
                strtolower($this->word);

        return $this;
    }

    /**
     * uppercase.
     *
     * @return object|string
     */
    public function uppercase()
    {
        $this->word = (defined('MB_ENABLED') && MB_ENABLED) ?
            mb_strtoupper($this->word) :
                strtoupper($this->word);

        return $this;
    }

    /**
     * showOrigin.
     *
     * @return string original word
     */
    public function showOrigin()
    {
        return $this->origin;
    }

    /**
     * convert.
     *
     * @param   string word
     *
     * @return object|string
     */
    public static function convert($word)
    {
        return parent::createInstance($word);
    }
}
