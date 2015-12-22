<?php namespace Viloveul\Core;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Database
 */

use Viloveul\Database;
use ReflectionClass;
use ReflectionException;
use Exception;

class Connector {

	protected static $groupDefault = 'default';

	protected static $connections = array();

	/**
	 * parseConfig
	 * 
	 * @access	protected
	 * @param	String|Array database configuration
	 * @return	Array configured
	 */

	protected static function parseConfiguration($params) {
		if (is_string($params)) {
			return array('dsn' => $params, 'username' => null, 'password' => null, 'prefix' => '');
		}

		$default = array(
			'driver' => 'mysql',
			'prefix' => '',
			'port' => 3306,
			'host' => 'localhost',
			'username' => 'root',
			'password' => '',
			'dbname' => '',
			'charset' => 'utf8',
			'collate' => 'utf8_general_ci'
		);

		$dbconf = array_merge($default, (array) $params);

		$username = $dbconf['username'];
		$password = $dbconf['password'];
		$driver = $dbconf['driver'];
		$prefix = $dbconf['prefix'];

		unset($dbconf['username'], $dbconf['password'], $dbconf['driver'], $dbconf['prefix']);
		$dsn = sprintf('%s:%s', $driver, http_build_query($dbconf, '', ';'));
		return compact('dsn', 'username', 'password', 'prefix');
	}

	/**
	 * defaultGroupSet
	 * 
	 * @access	public
	 * @param	String name
	 */

	public static function defaultGroupSet($name) {
		self::$groupDefault = trim($name);
	}

	/**
	 * defaultGroupGet
	 * 
	 * @access	public
	 * @return	String default connection group
	 */

	public static function defaultGroupGet() {
		return empty(self::$groupDefault) ? 'default' : (self::$groupDefault);
	}

	/**
	 * hasConnection
	 * 
	 * @access	public
	 * @param	String
	 * @return	Boolean
	 */

	public static function hasConnection($group) {
		return array_key_exists($group, self::$connections);
	}

	/**
	 * setConnection
	 * 
	 * @access	public
	 * @param	String group name
	 * @param	String|Array config
	 */

	public static function setConnection($group, $params = array()) {
		if (self::hasConnection($group))
			return false;

		$class = '\\App\\Drivers\\DB\\' . implode('', array_map('ucfirst', explode('-', $group)));

		if (class_exists($class)) {
			try {
				$check = new ReflectionClass($class);
				if (! $check->implementsInterface('\\Viloveul\\Database\\IConnection')) {
					throw new Exception("Database driver must implement of \\Viloveul\\Database\\IConnection");
				}
				self::$connections[$group] = $check->newInstance();
			} catch(ReflectionException $e) {
				throw new Exception($e->getMessage());
			}
		} else {
			$config = Configure::read('db', function($value) use($group, $params){
				return isset($value[$group]) ? $value[$group] : $params;
			});

			$dbconf = self::parseConfiguration($config);
			extract($dbconf);

			self::$connections[$group] = new Database\Manager($dsn, $username, $password, $prefix);
		}
	}

	/**
	 * getConnection
	 * 
	 * @access	public
	 * @param	String group name
	 * @return	Object connection (Scenario)
	 */

	public static function getConnection($group = null, $params = array()) {
		if (empty($group)) {
			$group = self::defaultGroupGet();
		}
		if (! self::hasConnection($group)) {
			self::setConnection($group, $params);
		}
		return self::$connections[$group];
	}

}
