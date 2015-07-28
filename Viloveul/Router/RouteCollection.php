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
	 * Implements of ArrayAccess
	 */

	public function offsetSet($key, $val) {
		$this->collections[$key] = $val;
	}

	/**
	 * Implements of ArrayAccess
	 */

	public function offsetGet($key) {
        return isset($this->collections[$key]) ? $this->collections[$key] : null;
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
