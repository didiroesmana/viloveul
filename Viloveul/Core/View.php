<?php namespace Viloveul\Core;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Core
 */

use Exception;
use ArrayAccess;

class View extends Object implements ArrayAccess {

	protected $filename = false;

	protected $vars = array();

	protected $useLayout = false;

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	String filename
	 * @param	Array vars
	 */

	public function __construct($filename, $vars = array()) {
		$this->filename = $filename;
		is_array($vars) && $this->set($vars);
	}

	/**
	 * Getter
	 * 
	 * @access	public
	 * @return	Any in Application::instance() properties
	 */

	public function __get($var) {
		$app =& Application::currentInstance();
		return $app[$var];
	}

	/**
	 * load
	 * 
	 * @access	protected
	 * @param	String filename
	 * @param	Array merge variable
	 * @return	void
	 */

	protected function load($__name, $__vars = array()) {
		$__parts = array_filter(explode('/', $__name), 'trim');
		$__path = Configure::apppath().'/Views/'.implode('/', $__parts).'.php';

		if ( is_file($__path) ) {

			extract($__vars);

			include $__path;

		}
	}

	/**
	 * buffer
	 * 
	 * @access	public
	 * @return	String buffered output
	 */

	public function buffer() {

		if ( ! $this->filename ) {
			return '';
		}

		ob_start();

		$this->load($this->filename, $this->vars);

		$__html = ob_get_contents();

		ob_end_clean();

		return $__html;
	}

	/**
	 * render
	 * 
	 * @access	public
	 */

	public function render() {
		$this->response->setOutput($this->buffer(), true);
	}

	/**
	 * get
	 * 
	 * @access	public
	 * @param	String variable name
	 * @param	Any default value
	 * @return	Any value
	 */

	public function get($var, $defaultValue = null) {
		if ( isset($this->vars[$var]) ) {
			return $this->vars[$var];
		}

		return $defaultValue;
	}

	/**
	 * set
	 * 
	 * @access	public
	 * @param	String variable name
	 * @param	Any value
	 */

	public function set($var, $value = null) {
		if ( is_string($var) ) {
			return $this->set(array($var => $value));
		}

		foreach ( (array) $var as $key => $val ) {
			$this->vars[$key] = $val;
		}

		return $this;
	}

	/**
	 * Implements of ArrayAccess
	 */

	public function offsetSet($var, $value) {
		if ( ! is_null($var) ) {
			$this->set($var, $value);
		}
	}

	/**
	 * Implements of ArrayAccess
	 */

	public function offsetGet($var) {
		return $this->get($var);
	}

	/**
	 * Implements of ArrayAccess
	 */

	public function offsetUnset($var) {
		if ( isset($this->vars[$var]) )
			unset($this->vars[$var]);
	}

	/**
	 * Implements of ArrayAccess
	 */

	public function offsetExists($var) {
		return isset($this->vars[$var]);
	}

	/**
	 * make
	 * 
	 * @access	public
	 * @param	[mixed] data paramters
	 * @return	String buffered output
	 */

	public static function make($name, array $vars = array()) {
		return self::createInstance($name, $vars)->buffer();
	}

}
