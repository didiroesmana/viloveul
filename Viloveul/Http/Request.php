<?php namespace Viloveul\Http;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Http
 */

use Viloveul\Core\Configure;

class Request {

	/**
	 * input
	 * 
	 * @access	public
	 * @param	String type
	 * @param	String name
	 * @param	Any default value
	 * @return	Any value
	 */

	public static function input($type, $name, $default = null) {
		$type = strtoupper($type);
		if ( $type === 'GET' ) {
			return isset($_GET[$name]) ? $_GET[$name] : $default;
		} elseif ( $type === 'POST' ) {
			return isset($_POST[$name]) ? $_POST[$name] : $default;
		} elseif ( $type === 'FILE' ) {
			return isset($_FILES[$name]) ? $_FILES[$name] : $default;
		}
		return $default;
	}

	/**
	 * isPost
	 * Check if request method is POST
	 * 
	 * @access	public
	 * @return	Boolean
	 */

	public static function isPost() {
		return self::isMethod('post');
	}

	/**
	 * isGet
	 * Check if request method is GET
	 * 
	 * @access	public
	 * @return	Boolean
	 */

	public static function isGet() {
		return self::isMethod('get');
	}

	/**
	 * isCli
	 * Check if request from command line
	 * 
	 * @access	public
	 * @return	Boolean
	 */

	public static function isCli() {
		return self::isMethod('cli');
	}

	/**
	 * isAjax
	 * Check if request from ajax
	 * 
	 * @access	public
	 * @return	Boolean
	 */

	public static function isAjax() {
		return self::isMethod('ajax');
	}

	/**
	 * isMethod
	 * Compare param with current request
	 * 
	 * @access	public
	 * @param	String method
	 * @return	Boolean
	 */

	public static function isMethod($method) {
		$method = strtolower($method);

		if ( $method == 'cli' ) {
			return defined('PHP_SAPI') && PHP_SAPI == 'cli';

		} elseif ( $method == 'ajax' || 'xhr' == $method ) {
			return Configure::server('http_x_requested_with', 'strtolower') == 'xmlhttprequest';
		}

		return Configure::server('request_method', 'strtolower') == $method;
	}

	/**
	 * httpReferer
	 * 
	 * @access	public
	 * @param	String default value
	 * @return	String http referer
	 */

	public static function httpReferer($default = '') {
		$getReferer = self::input('get', 'referer', $default);
		return Configure::server('http_referer', function($value) use($getReferer) {
			return empty($value) ? $getReferer : $value;
		});
	}

	/**
	 * createFromGlobals
	 * 
	 * @access	public
	 * @return	createFromGlobal
	 */

	public static function createFromGlobals() {
		$request = self::isCli() ?
			self::parseCommandLine() :
				self::parseRequestUri();

		if ( $index_page = Configure::read('index_page', 'trim') ) {
			if ( ! empty($index_page) && 0 === strpos($request, "/{$index_page}") ) {
				$request = substr($request, strlen($index_page) + 1);
			}
		}

		return $request;
	}

	/**
	 * parseRequestUri
	 * 
	 * @access	public
	 * @return	String request
	 */

	public static function parseRequestUri() {
		static $request = null;

		if ( is_null($request) ) {

			$request = '/';

			if ( ! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
				return $request;
			}

			$parts = parse_url($_SERVER['REQUEST_URI']);

			$path = isset($parts['path']) ? $parts['path'] : '/';
			$query = isset($parts['query']) ? $parts['query'] : '';
			$script = $_SERVER['SCRIPT_NAME'];

			if ( 0 === strpos($path, $script) ) {
				$path = substr($path, strlen($script));

			} else {
				$dirname = dirname($script);

				if ( 0 === strpos($path, $dirname) ) {
					$path = substr($path, strlen($dirname));
				}
			}

			$request = empty($path) ? '/' : $path;

			$_SERVER['QUERY_STRING'] = $query;
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		}

		return $request;
	}

	/**
	 * parseCommandLine
	 * 
	 * @access	public
	 * @return String request
	 */

	public static function parseCommandLine() {
		static $request = null;

		if ( is_null($request) ) {

			$request = '/';

			if ( ! isset($_SERVER['argv']) ) {
				return $request;
			}

			$path = isset($_SERVER['argv'][1]) ? '/'.trim($_SERVER['argv'][1], '/') : '/';
			$query = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '';

			$request = empty($path) ? '/' : $path;

			$_SERVER['QUERY_STRING'] = $query;
			parse_str($_SERVER['QUERY_STRING'], $_GET);
		}

		return $request;
	}

	/**
	 * currenturl
	 * 
	 * @access	public
	 * @return	String currentUrl
	 */

	public static function currenturl() {
		$path = self::createFromGlobals();
		$qs = Configure::server('query_string');

		return empty($qs) ?
			Configure::siteurl($path) :
				Configure::siteurl("{$path}?{$qs}");
	}

}
