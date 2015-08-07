<?php namespace Viloveul;

/**
 * @author		Fajrul Akbar Zuhdi
 * @package		Viloveul
 */

class App {

	const VERSION = '1.0.4';

	protected static $apppath;

	protected static $basedir;

	/**
	 * Constructor
	 * Keep silence
	 * 
	 * @access	private
	 */

	private function __construct() {
		// Keep silence
	}

	/**
	 * serve
	 * initialize front controller
	 * 
	 * @access	public
	 * @param	String application path
	 * @return	Object Viloveul\Core\Application
	 */

	public static function serve($path) {
		$apppath = realpath($path) or die('Application path does not exists');
		$basedir = realpath(dirname($_SERVER['SCRIPT_FILENAME']));

		self::$apppath = rtrim(str_replace('\\', '/', $apppath), '/');
		self::$basedir = rtrim(str_replace('\\', '/', $basedir), '/');

		spl_autoload_register(array(__CLASS__, 'autoload'), true, true);

		Core\Configure::useBaseSettings(
			array(
				'apppath' => self::$apppath,
				'basedir' => self::$basedir,
				'urlsuffix' => (defined('URL_SUFFIX') ? URL_SUFFIX : '')
			)
		);

		$app = new Core\Application();

		return $app;
	}

	/**
	 * autoload
	 * loader for called class
	 * 
	 * @access	public
	 * @param	String Classname
	 */

	public static function autoload($class) {
		$php = '.php';
		$class = ltrim($class, '\\');
		$name = str_replace('\\', '/', $class);

		if ( 0 === strpos($name, 'Viloveul/') ) {
			$location = dirname(__DIR__).'/'.$name.$php;
			is_file($location) && require_once($location);

		} elseif ( 0 === strpos($name, 'App/') ) {

			$location = self::$apppath.'/'.substr($name, 4).$php;
			is_file($location) && require_once($location);

		} elseif ( false === strpos($name, '/') ) {
			$location = self::$apppath.'/Packages';

			do {

				$location .= '/'.$name;

				if ( is_file($location.$php) ) {
					require_once($location.$php);
					break;
				}

			} while ( is_dir($location) );
		}
	}
}
