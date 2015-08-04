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

	protected $htmlAttribute;

	/**
	 * Constructor
	 */

	public function __construct($src, $text = null, $title = null) {
		$this->src = $src;
		$this->text = $text;
		$this->title = $title;
		$this->htmlAttribute = new HtmlAttribute;
	}

	/**
	 * To String
	 */

	public function __toString() {
		return $this->show();
	}

	/**
	 * Call
	 * 
	 * @access	public
	 * @param	String method name
	 * @param	Array arguments
	 * @return	void
	 */

	public function __call($method, $params) {
		if ( method_exists($this->htmlAttribute, $method) ) {
			call_user_func_array(array(&$this->htmlAttribute, $method), $params);
		}
		return $this;
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

		if ( $this->autoActive === true ) {
			if ( $href == Request::currenturl() ) {
				$this->addAttr('class', 'active');
			}
		}

		$this->addAttr('title', $title)->addAttr('href', $src);

		return "<a{$this->htmlAttribute}>{$text}</a>";
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
