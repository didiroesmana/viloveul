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
	 * @return	void
	 */

	public function __construct() {
		self::$loadedControllers[self::classname()] = $this;
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

	public static function fire($requestSegment, $print = true) {
		$request = preg_split('/\//', $requestSegment, -1, PREG_SPLIT_NO_EMPTY);
		$method = 'action' . str_replace(' ', '', ucwords(str_replace('-', ' ', $request)));

		if (! self::hasMethod($method))
			return false;

		$classname = self::classname();

		try {

			$controller = isset(self::$loadedControllers[$classname]) ?
				self::$loadedControllers[$classname] :
					self::createInstance();

			$ref = new ReflectionMethod($classname, $method);
			$output = $ref->invokeArgs($controller, $request);

			if (! is_null($output)) {

				if (true !== $print) {
					return $output;
				}

				print $output;

				return true;
			}

		} catch (ReflectionException $e) {
			Debugger::handleException($e);
		}
	}

}
