<?php namespace Viloveul\Core;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Core
 */

use ReflectionMethod;
use ReflectionException;

abstract class Controller extends Object {

	private static $loadedControllers = array();

	/**
	 * Constructor
	 * 
	 * @access	public
	 */

	public function __construct() {
		
	}

	/**
	 * To String
	 * 
	 * @access	public
	 * @return	String classname
	 */

	public function __toString() {
		return self::classname();
	}

	/**
	 * Getter
	 * 
	 * @access	public
	 * @param	String name
	 * @return	application collections
	 */

	public function __get($name) {
		$app =& Application::currentInstance();
		return $app[$name];
	}

	/**
	 * fire
	 * run controller as module
	 * 
	 * @access	public
	 * @param	String segment
	 * @return	String output
	 */

	public static function fire($requestSegment, $return = false) {
		$request = preg_split('/\//', $requestSegment, -1, PREG_SPLIT_NO_EMPTY);
		$method = 'action' . implode('', array_map('ucfirst', explode('-', array_shift($request))));

		if ( ! self::hasMethod($method) )
			return false;

		$class = self::classname();

		if ( ! isset(self::$loadedControllers[$class]) ) {
			self::$loadedControllers[$class] = new $class;
		}

		try {

			$ref = new ReflectionMethod($class, $method);
			$output = $ref->invokeArgs(self::$loadedControllers[$class], $request);

			if ( ! is_null($output) ) {
				if ( true === $return )
					return $output;

				echo $output;

				return true;
			}

		} catch (ReflectionException $e) {
			die($e->getMessage());
		}
	}

}
