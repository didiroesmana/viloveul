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

	protected $directory = null;

	protected static $globalVars = array();

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	String filename
	 * @param	Array vars
	 */

	public function __construct($filename, array $vars = array()) {
		$this->filename = $filename;
		is_array($vars) && $this->set($vars);
	}

	/**
	 * To String
	 * 
	 * @access	public
	 * @return	String rendered output
	 */

	public function __toString() {
		return $this->render();
	}

	/**
	 * make
	 * 
	 * @access	public
	 * @param	[mixed] data paramters
	 * @return	String rendered output
	 */

	public static function make($name, array $vars = array()) {
		return self::createInstance($name, $vars)->render();
	}

	/**
	 * withGlobalVar
	 * 
	 * @access	public
	 * @param	String|Array var(s) name
	 * @param	[mixed]
	 * @return	void
	 */

	public static function dataGlobalSet($data, $value = null) {
		if ( is_string($data) ) {
			return self::dataGlobalSet(array($data => $value));
		}

		foreach ( (array) $data as $var => $val ) {
			if ( is_null($val) && isset(self::$globalVars[$var]) ) {
				unset(self::$globalVars[$var]);
			} else {
				self::$globalVars[$var] = $val;
			}
		}
	}

	/**
	 * dataGlobalGet
	 * 
	 * @access	public
	 * @param	String var
	 * @param	Any default value(s)
	 * @return	Any
	 */

	public static function dataGlobalGet($var, $defaultValue = null) {
		return isset(self::$globalVars[$var]) ? self::$globalVars[$var] : $defaultValue;
	}

	/**
	 * changeDirectory
	 * 
	 * @access	public
	 * @param	String realpath
	 * @return	void
	 */

	public function changeDirectory($path) {
		if ( is_dir($path) ) {
			$this->directory = rtrim(realpath($path), '/');
		}
		return $this;
	}

	/**
	 * render
	 * 
	 * @access	public
	 * @param	Callable Callback
	 * @return	String rendered output
	 */

	public function render($callbackFilter = null) {
		$allvars = array_merge(self::$globalVars, $this->vars);

		$output = $this->load($this->filename, $allvars);

		return is_callable($callbackFilter) ?
			call_user_func($callbackFilter, $output, $this) :
				$output;
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
		} elseif ( isset(self::$globalVars[$var]) ) {
			return self::$globalVars[$var];
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
	 * load
	 * 
	 * @access	protected
	 * @param	String filename
	 * @param	Array merge variable
	 * @return	void
	 */

	protected function load($__name, $__vars = array()) {
		$__dir = is_null($this->directory) ?
			(Configure::apppath().'/Views') :
				$this->directory;

		$__parts = array_filter(explode('/', $__name), 'trim');
		$__file = $__dir.'/'.implode('/', $__parts).'.php';

		if ( ! is_file($__file) ) {
			throw new Exception('Unable to locate view : ' . $__file);
		}

		ob_start();
			extract($__vars);

			include $__file;

			$__html = ob_get_contents();
		ob_end_clean();

		return $__html;
	}

}
