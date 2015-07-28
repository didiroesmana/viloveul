<?php namespace Viloveul\Core;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Core
 */

use Viloveul\Database\Connector;

abstract class Model extends Object {

	private static $modelCollections = array();

	protected $db;

	/**
	 * Constructor
	 */

	public function __construct() {
		$this->db = Connector::getConnection();
	}

	/**
	 * forge
	 * 
	 * @access	public
	 * @param	[mixed] Boolean or Object
	 * @return	Object new|old Instance
	 */

	public static function forge($param = true) {
		$class = self::classname();

		if ( false === $param ) {
			return self::createInstance();

		} elseif ($param instanceof $class) {
			self::$modelCollections[$class] = $param;
		}

		if ( ! isset(self::$modelCollections[$class]) ) {
			self::$modelCollections[$class] = self::createInstance();
		}

		return self::$modelCollections[$class];
	}

}
