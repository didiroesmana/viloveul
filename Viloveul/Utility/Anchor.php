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

	protected $src = '#';

	protected $text = '';

	protected $title = '';

	protected $autoActive = false;

	protected $classes = array();

	protected $dataAttributes = array();

	protected $idAttribute;

	/**
	 * Constructor
	 */

	public function __construct($src, $text = null, $title = null) {
		$this->src = $src;
		$this->text = $text;
		$this->title = $title;
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
		if ($this->src == '#') {
			$src = '#';
		} else {
			$src = !preg_match('#^\w+\:\/\/#', $this->src) ?
				Configure::siteurl($this->src) :
					$this->src;
		}

		$text = empty($this->text) ? $src : $this->text;

		$title = empty($this->title) ? $text : $this->title;

		$html = '<a href="' . $src . '" title="' . $title . '"';

		if ( ! empty($this->idAttribute) ) {
			$html .= sprintf(' id="%s"', $this->idAttribute);
		}

		if ( $this->autoActive === true ) {
			if ( $href == Request::currenturl() ) {
				$this->classes[] = 'active';
			}
		}

		if ( $this->classes ) {
			$classes = array_filter($this->classes, 'trim');
			$html .= ' class="' . implode(' ', $classes) . '"';
		}

		if ( ! empty($this->dataAttributes) ) {
			foreach ( $this->dataAttributes as $attrK => $attrV ) {
				$html .= sprintf(' data-%s="%s"', $attrK, $attrV);
			}
		}

		$html .= '>' . $text . '</a>';
		return $html;
	}

	/**
	 * id
	 * 
	 * @access	public
	 * @param	String html id
	 * @return	void
	 */

	public function id($id) {
		$this->idAttribute = $id;
		return $this;
	}

	/**
	 * addClass
	 * 
	 * @access	public
	 * @param	String class
	 * @param	[mixed] String classes
	 * @return	void
	 */

	public function addClass($class) {
		$params = func_get_args();
		foreach ( (array) $params as $value ) {
			$this->classes[] = $value;
		}
		return $this;
	}

	/**
	 * removeClass
	 * 
	 * @access	public
	 * @param	String class
	 * @param	[mixed] String classes
	 * @return	void
	 */

	public function removeClass($class) {
		if ( $classes = $this->classes ) {
			$params = func_get_args();
			$this->classes = array_diff($classes, $params);
		}
		return $this;
	}

	/**
	 * data
	 * 
	 * @access	public
	 * @param	String name
	 * @param	String value
	 * @return	void
	 */

	public function data($name, $value = null) {
		if ( is_string($name) ) {
			return $this->data(array($name => $value));
		}

		foreach ( (array) $name as $key => $val ) {
			$this->dataAttributes[$key] = $val;
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

	public static function create($src, $text = null, $title = null) {
		return self::createInstance($src, $text, $title);
	}

}
