<?php namespace Viloveul\Router;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Router
 */

use ArrayAccess;
use Iterator;

class RouteCollection implements ArrayAccess, Iterator {

	protected $collections = array();

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	Array routes
	 * @return	void
	 */

	public function __construct($routes = array()) {
		empty($routes) or $this->add($routes);
	}

	/**
	 * add
	 * 
	 * @access	public
	 * @param	String|Array
	 * @param	String|Callable handler
	 * @return	void
	 */

	public function add($data, $value = null) {
		if ( is_string($data) ) {
			return $this->add(array($data => $value));
		}

		foreach ( (array) $data as $key => $target ) {
			if ( ! is_null($key) ) {
				$this->collections[$key] = $target;
			}
		}

		return $this;
	}

	/**
	 * fetch
	 * 
	 * @access	public
	 * @param	String name
	 * @return	String|Callable handler
	 */

	public function fetch($key) {
		return isset($this->collections[$key]) ? $this->collections[$key] : null;
	}

	/**
	 * Implements of ArrayAccess
	 */

	public function offsetSet($key, $val) {
		$this->add(array($key => $val));
	}

	/**
	 * Implements of ArrayAccess
	 */

	public function offsetGet($key) {
		return $this->fetch($key);
	}

	/**
	 * Implements of ArrayAccess
	 */

	public function offsetUnset($key) {
		if ( isset($this->collections[$key]) ) {
			unset($this->collections[$key]);
		}
	}

	/**
	 * Implements of ArrayAccess
	 */

	public function offsetExists($key) {
		return isset($this->collections[$key]);
	}

	/**
	 * Implements of Iterator
	 */

	public function current() {
		return current($this->collections);
	}

	/**
	 * Implements of Iterator
	 */

	public function key() {
		return key($this->collections);
	}

	/**
	 * Implements of Iterator
	 */

	public function next() {
		next($this->collections);
	}

	/**
	 * Implements of Iterator
	 */

	public function rewind() {
		reset($this->collections);
	}

	/**
	 * Implements of Iterator
	 */

	public function valid() {
		return null !== $this->key();
	}

}
