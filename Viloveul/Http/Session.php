<?php namespace Viloveul\Http;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Http
 */

use ArrayAccess;

class Session implements ArrayAccess {

	protected $sessionName = 'zafex';

	protected $flashVars = array();

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

		if ( session_id() === '' ) {
			session_name($this->sessionName);
			session_start();
		}

		if ( ! isset($_SESSION['__vars']) ) {
			$_SESSION['__vars'] = array();

		} elseif ( ! is_array($_SESSION['__vars']) ) {
			unset($_SESSION['__vars']);
			$_SESSION['__vars'] = array();
		}

		if ( isset($_SESSION['__flashdata']) ) {
			$this->flashVars = (array) $_SESSION['__flashdata'];
			unset($_SESSION['__flashdata']);
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
			$this->set(array($name => $value));
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
		return $this->get($name, null);
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
		if ( is_string($data) ) {
			return $this->set(array($data => $value));
		}

		foreach ( (array) $data as $key => $val ) {
			$_SESSION['__vars'][$key] = $val;
		}

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
	 * createFlashdata
	 * 
	 * @access	public
	 * @param	String key
	 * @param	any
	 * @return	void
	 */

	public function createFlashdata($data, $value = null) {
		if ( is_string($data) ) {
			return $this->createFlashdata(array($data => $value));
		}

		foreach ( (array) $data as $key => $val ) {
			$_SESSION['__flashdata'][$key] = $val;
		}
		return $this;
	}

	/**
	 * readFlashdata
	 * 
	 * @access	public
	 * @param	String key
	 * @param	any
	 * @return	any
	 */

	public function readFlashdata($key, $default = null) {
		return isset($this->flashVars[$key]) ?
			$this->flashVars[$key] :
				$default;
	}

	/**
	 * keepFlashdata
	 * 
	 * @access	public
	 * @param	String key
	 * @return	boid
	 */

	public function keepFlashdata($key) {
		if ( isset($this->flashVars[$key]) ) {
			$_SESSION['__flashdata'][$key] = $this->flashVars[$key];
		}
		return $this;
	}

}
