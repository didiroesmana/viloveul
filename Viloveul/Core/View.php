<?php namespace Viloveul\Core;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Core
 */

use Exception;
use ArrayAccess;

class View extends Object implements ArrayAccess {

	private static $globalVars = array();

	protected $filename = false;

	protected $vars = array();

	protected $directory = null;

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
		self::dataGlobalSet($vars, null);
		return self::createInstance($name)->render();
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

	public function render($__local281291callbackFilter = null) {
		if ( is_null($this->directory) ) {
			$this->directory = APPPATH . '/Views';
		}

		$__local281291vars = array_merge(self::$globalVars, $this->vars);
		$__local281291fileparts = array_filter(explode('/', $this->filename), 'trim');
		$__local281291filename = $this->directory . '/'.implode('/', $__local281291fileparts).'.php';

		if ( ! is_file($__local281291filename) ) {
			throw new Exception('Unable to locate view : ' . $__local281291filename);
		}

		$__local281291contentFile = $this->loadContentFile($__local281291filename);

		extract($__local281291vars);

		ob_start();

		eval('?>' . $__local281291contentFile);

		$__local281291outputRendering = ob_get_clean();

		$__local281291trimOutputRendering = trim($__local281291outputRendering);

		return is_callable($__local281291callbackFilter) ?
			call_user_func($__local281291callbackFilter, $__local281291trimOutputRendering, $this) :
				$__local281291trimOutputRendering;
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
	 * filterLoadedContents
	 * 
	 * @access	protected
	 * @param	String content file
	 * @return	String
	 */

	protected function filterLoadedContents($contents = '') {
		if ( strpos($contents, '{{@') !== false && false !== strpos($contents, '}}') ) {
			$contents = preg_replace_callback(
				'#\{\{\@(.+)\}\}#U',
				array($this, 'handleContentFiltered'),
				$contents
			);
		}
		return $contents;
	}

	/**
	 * handleContentFiltered
	 * 
	 * @access	protected
	 * @param	Array matches
	 * @return	String
	 */

	protected function handleContentFiltered($matches) {
		$filename = trim($matches[1]);
		$path = "{$this->directory}/{$filename}.php";

		return is_file($path) ?
			$this->loadContentFile($path) :
				$matches[0];
	}

	/**
	 * loadContentFile
	 * 
	 * @access	protected
	 * @param	String filename
	 * @return	String
	 */

	protected function loadContentFile($filename) {
		return $this->filterLoadedContents(
			file_get_contents($filename)
		);
	}

}
