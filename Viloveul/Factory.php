<?php namespace Viloveul;

/**
 * @author		Fajrul Akbar Zuhdi
 * @package		Viloveul
 */

class Factory {

	const SYSVERSION = '1.0.4';

	/**
	 * Constructor
	 * Keep silence
	 * 
	 * @access	private
	 */

	private function __construct() {
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

		define('APPPATH', rtrim(str_replace('\\', '/', $apppath), '/'));
		define('BASEDIR', rtrim(str_replace('\\', '/', $basedir), '/'));

		spl_autoload_register(array(__CLASS__, 'autoload'), true, true);

		is_file(APPPATH.'/configs.php') and Core\Configure::write(include APPPATH.'/configs.php');

		Core\Debugger::registerErrorHandler();
		Core\Debugger::registerExceptionHandler();

		register_shutdown_function(function(){
			$error = error_get_last();

			if (isset($error) && ($error['type'] & (E_ERROR|E_PARSE|E_CORE_ERROR|E_CORE_WARNING|E_COMPILE_ERROR|E_COMPILE_WARNING))) {
				Core\Debugger::handleError($error['type'], $error['message'], $error['file'], $error['line']);
			}
		});

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

			$location = APPPATH.'/'.substr($name, 4).$php;
			is_file($location) && require_once($location);

		} elseif ( false === strpos($name, '/') ) {
			$location = APPPATH.'/Packages';

			/**
			 * search file deeper
			 * /var/www/public_html/your_app/Packages/name/name/.../name/name.php
			 */

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
