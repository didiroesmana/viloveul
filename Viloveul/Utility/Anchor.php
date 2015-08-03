<?php namespace Viloveul\Utility;

/**
 * @author		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

use Viloveul\Core\Configure;
use Viloveul\Core\Object;
use Viloveul\Http\Request;

class Anchor extends Object {

	protected $href = '#';

	protected $text;

	protected $autoActive = false;

	/**
	 * Constructor
	 */

	public function __construct($href, $text = null, array $attributes = array()) {
		$this->href = $href;
		$this->text = empty($text) ? $href : $text;
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
			$href = !preg_match('#^\w+\:\/\/#', $this->href) ?
				Configure::siteurl($this->href) :
					$this->href;
		}

		$html = '<a href="' . $href . '"';

		if ( $this->autoActive === true ) {
			if ( $href == Request::currenturl() ) {
				$this->classes[] = 'active';
			}
		}

		if ( $this->classes ) {
			$classes = array_filter($this->classes, 'trim');
			$html .= ' class="' . implode(' ', $classes) . '"';
		}

		$html .= '>' . $this->text . '</a>';
		return $html;
	}

	/**
	 * attrId
	 * 
	 * @access	public
	 * @param	String html id
	 * @return	void
	 */

	public function attrId($data) {
		$this->id = $data;
		return $this;
	}

	/**
	 * attrClass
	 * 
	 * @access	public
	 * @param	String|Array classes
	 * @return	void
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

	/**
	 * autoActiveClass
	 * 
	 * @access	public
	 * @param	Boolean
	 * @return	void
	 */

	public function autoActiveClass($value) {
		if ( is_boolean($value) ) {
			$this->autoActive = $value;
		}
		return $this;
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
