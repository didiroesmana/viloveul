<?php namespace Viloveul\Utility;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

use Viloveul\Core\Object;
use ReflectionClass;

/**
 * Example to use :
 * 
 * $word = "Hello World";
 * $newWord = \Viloveul\Utility\Inflector::convert($word)->toUnderscore();
 * 
 * echo $newWord;
 * 
 * Result is "hello_world"
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
		$this->word = preg_replace('#[^a-z0-9\-\.\:]+#', $separator, $word);
		return $this;
	}

	/**
	 * toUnderscore
	 * 
	 * @access	public
	 * @return	Object|String
	 */

	public function toUnderscore() {
		$this->word = preg_replace('/[\s]+/', '_', $this->word);
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
	 * convert
	 * 
	 * @access	public
	 * @param	String word
	 * @return	Object|String
	 */

	public static function convert($word) {
		return self::createInstance($word);
	}

}
