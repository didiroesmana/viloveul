<?php namespace Viloveul\Http;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Http
 */

use ArrayAccess;

class Session implements ArrayAccess {

	protected $sessionName = 'zafex';

	/**
	 * Constructor
	 */

	public function __construct($sessionName) {
		$this->sessionName = $sessionName;

		session_name($this->sessionName);

		@session_start();

		if ( ! isset($_SESSION['__vars']) ) {
			$_SESSION['__vars'] = array();

		} elseif ( ! is_array($_SESSION['__vars']) ) {
			unset($_SESSION['__vars']);
			$_SESSION['__vars'] = array();
		}
	}

	/**
	 * all
	 * 
	 * @access	public
	 * @return	Array
	 */

	public function &all() {
		return $_SESSION['__vars'];
	}


	/**
	 * set
	 * implement of ArrayAccess
	 * 
	 * @access	public
	 * @param	String name
	 * @param	value
	 */

	public function offsetSet($name, $value) {
		if ( ! is_null($name) ) {
			$this->pushData(array($name => $value));
		}
	}

	/**
	 * get
	 * implement of ArrayAccess
	 * 
	 * @access	public
	 * @param	String name
	 * @return	Any
	 */

	public function offsetGet($name) {
		return $this->read($name, null);
	}

	/**
	 * unset
	 * implement of ArrayAccess
	 * 
	 * @access	public
	 * @param	String name
	 */

	public function offsetUnset($name) {
		return $this->delete($name);
	}

	/**
	 * exists
	 * implement of ArrayAccess
	 * 
	 * @access	public
	 * @param	String name
	 * @return	Boolean
	 */

	public function offsetExists($name) {
		return $this->has($name);
	}

	/**
	 * set
	 * 
	 * @access	public
	 * @param	String name
	 * @param	value
	 */

	public function set($data, $value = null) {
		$this->pushData($data, $value);

		return $this;
	}

	/**
	 * get
	 * 
	 * @access	public
	 * @param	String name
	 * @param	Any default value
	 * @return	Any
	 */

	public function get($name, $default = null) {
		return $this->has($name) ?
			$_SESSION['__vars'][$name] :
				$default;
	}

	/**
	 * delete
	 * 
	 * @access	public
	 * @param	String name
	 */

	public function delete($name) {
		if ( $this->has($name) ) {
			unset($_SESSION['__vars'][$name]);
		}
	}

	/**
	 * has
	 * 
	 * @access	public
	 * @param	String name
	 * @return	Boolean
	 */

	public function has($name) {
		return isset($_SESSION['__vars'][$name]);
	}

	/**
	 * destroy
	 * 
	 * @access	public
	 */

	public function destroy() {
		session_unset();
		session_destroy();
		session_write_close();
	}

	/**
	 * pushData
	 * 
	 * @access	protected
	 * @param	Array|String data
	 * @param	[mixed]
	 */

	protected function pushData($data, $val = null) {
		if ( ! is_array($data) ) {
			$this->pushData(array($data => $val));
		} else {
			foreach ( (array) $data as $name => $value ) {
				$_SESSION['__vars'][$name] = $value;
			}
		}
	}

}
