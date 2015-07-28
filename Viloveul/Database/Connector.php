<?php namespace Viloveul\Database;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Database
 */

use Viloveul\Core\Configure;

class Connector {

	protected static $defaultConnection = 'default';

	protected static $connections = array();

	/**
	 * parseConfig
	 * 
	 * @access	protected
	 * @param	String|Array database configuration
	 * @return	Array configured
	 */

	protected static function parseConfig($params) {
		if ( is_string($params) ) {
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
		$dsn = "{$driver}:" . http_build_query($dbconf, '', ';');
		return compact('dsn', 'username', 'password', 'prefix');
	}

	/**
	 * setDefaultConnection
	 * 
	 * @access	public
	 * @param	String name
	 */

	public static function setDefaultConnection($name) {
		self::$defaultConnection = trim($name);
	}

	/**
	 * getDefaultConnection
	 * 
	 * @access	public
	 * @return	String default connection group
	 */

	public static function getDefaultConnection() {
		return empty(self::$defaultConnection) ? 'default' : (self::$defaultConnection);
	}

	/**
	 * setConnection
	 * 
	 * @access	public
	 * @param	String group name
	 */

	public static function setConnection($group) {
		if ( isset(self::$connections[$group]) )
			return false;

		$class = '\\App\\Drivers\\DB\\' . implode('', array_map('ucfirst', explode('-', $group)));

		if ( class_exists($class) ) {
			self::$connections[$group] = new $class;
		} else {

			$config = Configure::get('database', function($value) use ($group){
				return isset($value[$group]) ? $value[$group] : array();
			});

			$dbconf = self::parseConfig($config);

			extract($dbconf);

			self::$connections[$group] = new Scenario($dsn, $username, $password, $prefix);
		}
	}

	/**
	 * getConnection
	 * 
	 * @access	public
	 * @param	String group name
	 * @return	Object connection (Scenario)
	 */

	public static function getConnection($group = null) {
		if ( empty($group) ) {
			$group = self::getDefaultConnection();
		}
		if ( ! isset(self::$connections[$group]) ) {
			self::setConnection($group);
		}
		return self::$connections[$group];
	}

}
