<?php namespace Viloveul\Utility;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

use Viloveul\Core\Object;

/**
 * Example to use :
 * 
 * $word = "Hello World";
 * echo \Viloveul\Utility\Inflector::convert($word)->toUnderscore();
 * ##Result is "Hello_World"
 * 
 * echo \Viloveul\Utility\Inflector::convert($word)->toUnderscore()->lowercase();
 * ##Result is "hello_world"
 */

class Inflector extends Object {

	protected $word;

	protected $origin;

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	String word
	 */

	public function __construct($word) {
		$this->word = $this->origin = $word;
	}

	/**
	 * To String
	 * 
	 * @access	public
	 * @return	String current word
	 */

	public function __toString() {
		return $this->word;
	}

	/**
	 * toCamelize
	 * 
	 * @access	public
	 * @param	String separator to convert
	 * @return	Object|String
	 */

	public function toCamelize($separator = null) {
		$seps = array('-', '_');
		if ( ! is_null($separator) ) {
			$seps = func_get_args();
		}
		$words = ucwords(str_replace($seps, ' ', $this->word));
		$this->word = str_replace(' ', '', $words);
		return $this;
	}

	/**
	 * toSlug
	 * 
	 * @access	public
	 * @param	String separator to used
	 * @return	Object|String
	 */

	public function toSlug($separator, $lowercase = true) {
		$word = (true === $lowercase) ? $this->lowercase() : $this->word;
		$this->word = preg_replace('#[^a-zA-Z0-9\-\.\:]+#', $separator, $word);
		return $this;
	}

	/**
	 * toUnderscore
	 * 
	 * @access	public
	 * @return	Object|String
	 */

	public function toUnderscore() {
		$this->word = trim(str_replace(' ', '', preg_replace('/(?:\\w)([a-z]+)/', '_\\0', $this->word)), '_');
		return $this;
	}

	/**
	 * lowercase
	 * 
	 * @access	public
	 * @return	Object|String
	 */

	public function lowercase() {
		$this->word = (defined('MB_ENABLED') && MB_ENABLED) ?
			mb_strtolower($this->word) :
				strtolower($this->word);

		return $this;
	}

	/**
	 * uppercase
	 * 
	 * @access	public
	 * @return	Object|String
	 */

	public function uppercase() {
		$this->word = (defined('MB_ENABLED') && MB_ENABLED) ?
			mb_strtoupper($this->word) :
				strtoupper($this->word);

		return $this;
	}

	/**
	 * showOrigin
	 * 
	 * @access	public
	 * @return	String original word
	 */

	public function showOrigin() {
		return $this->origin;
	}

	/**
	 * convert
	 * 
	 * @access	public
	 * @param	String word
	 * @return	Object|String
	 */

	public static function convert($word) {
		return parent::createInstance($word);
	}

}
