<?php namespace Viloveul\Utility;

/**
 * @author		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

use Viloveul\Core\Configure;
use Viloveul\Core\Object;

class Anchor extends Object {

	protected $href = '#';

	protected $text;

	/**
	 * Constructor
	 */

	public function __construct($href, $text = null, array $attributes = array()) {
		$this->href = $href;
		$this->text = $text;
	}

	/**
	 * To String
	 */

	public function __toString() {
		return $this->show();
	}

	/**
	 * show
	 * 
	 * @access	public
	 * @return	String anchor element
	 */

	public function show() {
		if ($this->href == '#') {
			$href = '#';
		} else {
			$href = !preg_match('#^\W\:\/\/#', $this->href) ?
				Configure::siteurl($this->href) :
					$this->href;
		}

		$html = '<a href="' . $href . '"';
	}

	/**
	 * attrClass
	 * 
	 * @access	public
	 * @param	String|Array classes
	 */

	public function attrClass($data) {
		if ( is_string($data) ) {
			return $this->attrClass(explode(' ', $data));
		}
		foreach ( (array) $data as $value ) {
			$this->classes[] = $value;
		}
		return $this;
	}

	public function attrId($data) {
		$this->id = $data;
	}

	/**
	 * create
	 * 
	 * @access	public
	 * @param	String href
	 * @return	Object class
	 */

	public static function create($href, $text = null, array $attributes = array()) {
		return self::createInstance($href, $text);
	}

}
