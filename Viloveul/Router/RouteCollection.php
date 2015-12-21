<?php namespace Viloveul\Router;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Router
 */

use Iterator;

class RouteCollection implements Iterator {

	protected $collections = array();

	/**
	 * add
	 *
	 * @access	public
	 * @param	String
	 * @param	String|Object
	 */

	public function add($pattern, $handler) {
		$this->collections[$pattern] = $handler;
	}

	/**
	 * has
	 *
	 * @access	public
	 * @param	String
	 * @return	Boolean
	 */

	public function has($pattern) {
		return isset($pattern, $this->collections);
	}

	/**
	 * fetch
	 *
	 * @access	public
	 * @param	String
	 * @return	String|Object|NULL
	 */

	public function fetch($pattern) {
		return $this->has($pattern) ?
			$this->collections[$pattern] :
				null;
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
