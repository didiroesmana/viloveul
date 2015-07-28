<?php namespace Viloveul\Core;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Core
 */

use ReflectionClass;
use ReflectionException;
use Exception;

abstract class Object {

	/**
	 * classname
	 * 
	 * @access	public
	 * @return	String called classname
	 */

	final public static function classname() {
		return get_called_class();
	}

	/**
	 * availableMethods
	 * 
	 * @access	public
	 * @return	Array method lists
	 */

	final public static function availableMethods() {
		return get_class_methods(self::classname());
	}

	/**
	 * hasMethod
	 * 
	 * @access	public
	 * @param	String method name
	 * @return	Boolean
	 */

	final public static function hasMethod($methodname) {
		return in_array($methodname, self::availableMethods(), true);
	}

	/**
	 * createInstance
	 * 
	 * @access	public
	 * @param	[mixed]
	 * @return	Object
	 */

	final public static function createInstance($param = null) {
		$reflectionClass = new ReflectionClass(self::classname());

		return is_null($param) ?
			$reflectionClass->newInstance() :
				$reflectionClass->newInstanceArgs(func_get_args());
	}

}
