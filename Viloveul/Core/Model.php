<?php namespace Viloveul\Core;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Core
 */

use Viloveul\Database\Connector;
use Viloveul\Database\IConnection;

abstract class Model extends Object {

	private static $modelCollections = array();

	protected $db;

	/**
	 * Constructor
	 */

	public function __construct($connection = null) {
		$this->db = ($connection instanceof IConnection) ? $connection : (Connector::getConnection());
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
			self::$modelCollections[$class] = self::createInstance($param);
		}

		return self::$modelCollections[$class];
	}

}
