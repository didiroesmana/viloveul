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
		$defaultOptions = array(
			'name' => null,
			'lifetime' => 0,
			'path' => '/',
			'domain' => null,
			'secure' => true,
			'httponly' => false
		);

		if ( is_string($sessionName) ) {
			$this->options = array_merge($defaultOptions, array('name' => $sessionName));
		} else {
			$this->options = array_merge($defaultOptions, (array) $sessionName);
		}

		if ( empty($this->options['name']) ) {
			$this->options['name'] = 'zafex';
		}

		$this->sessionName = $sessionName;

		register_shutdown_function('session_write_close');

		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			session_name($this->sessionName);
			session_start();
		}

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
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(),
				'',
				time() - 42000,
				$params["path"],
				$params["domain"],
				$params["secure"],
				$params["httponly"]
			);
		}
		$_SESSION = array();
		session_unset();
		session_destroy();
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
