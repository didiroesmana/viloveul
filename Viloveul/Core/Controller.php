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

	public static function fire($requestSegment) {
		$request = preg_split('/\//', $requestSegment, -1, PREG_SPLIT_NO_EMPTY);
		$method = 'action' . implode('', array_map('ucfirst', explode('-', array_shift($request))));

		if ( ! self::hasMethod($method) )
			return false;

		$class = self::classname();

		if ( ! isset(self::$loadedControllers[$class]) ) {
			self::$loadedControllers[$class] = new $class;
		}

		try {
			ob_start();

			$ref = new ReflectionMethod($class, $method);
			$ref->invokeArgs(self::$loadedControllers[$class], $request);
			self::$loadedControllers[$class]->response->send();
			$output = ob_get_clean();

			if ( is_null($output) ) {
				return false;
			}

			echo $output;
			return true;

		} catch (ReflectionException $e) {
			die( $e->getMessage() );
		}
	}

}
