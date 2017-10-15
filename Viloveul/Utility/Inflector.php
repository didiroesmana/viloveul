<?php

namespace Viloveul\Utility;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

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
class Inflector
{
    /**
     * @var mixed
     */
    protected $origin;

    /**
     * @var mixed
     */
    protected $word;

    /**
     * @param $word
     */
    public function __construct($word)
    {
        $this->word = $this->origin = $word;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->word;
    }

    /**
     * @param $word
     */
    public static function convert($word)
    {
        return new static($word);
    }

    /**
     * @return mixed
     */
    public function lowercase()
    {
        $this->word = (defined('MB_ENABLED') && MB_ENABLED) ? mb_strtolower($this->word) : strtolower($this->word);

        return $this;
    }

    /**
     * @return mixed
     */
    public function showOrigin()
    {
        return $this->origin;
    }

    /**
     * @param  $separator
     * @return mixed
     */
    public function toCamelize($separator = null)
    {
        $seps = ['-', '_'];
        if (!is_null($separator)) {
            $seps = func_get_args();
        }
        $words = ucwords(str_replace($seps, ' ', $this->word));
        $this->word = str_replace(' ', '', $words);

        return $this;
    }

    /**
     * @param  $separator
     * @param  $lowercase
     * @return mixed
     */
    public function toSlug($separator, $lowercase = true)
    {
        $word = (true === $lowercase) ? $this->lowercase() : $this->word;
        $this->word = preg_replace('#[^a-zA-Z0-9\-\.\:]+#', $separator, $word);

        return $this;
    }

    /**
     * @return mixed
     */
    public function toUnderscore()
    {
        $this->word = trim(str_replace(' ', '', preg_replace('/(?:\\w)([a-z]+)/', '_\\0', $this->word)), '_');

        return $this;
    }

    /**
     * @return mixed
     */
    public function uppercase()
    {
        $this->word = (defined('MB_ENABLED') && MB_ENABLED) ? mb_strtoupper($this->word) : strtoupper($this->word);

        return $this;
    }
}
