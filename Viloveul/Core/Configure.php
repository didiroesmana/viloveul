<?php namespace Viloveul\Core;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Core
 */

class Configure {

	protected static $configs = array();

	private static $definedGlobals = array();

	/**
	 * Call Statically
	 * 
	 * @access	public
	 * @param	String name
	 * @param	Array arguments
	 * @return	Any
	 */

	public static function __callStatic($calledName, $params) {
		if ( ! array_key_exists($calledName, self::$definedGlobals) ) {
			return isset($params[0]) ? $params[0] : null;
		}

		$data = self::$definedGlobals[$calledName];

		if ( is_array($data) ) {
			foreach ( $params as $key ) {
				if ( isset($data[$key]) ) {
					$data = $data[$key];
				}
			}
		}

		return $data;
	}

	/**
	 * useBaseSettings
	 * 
	 * @access	public
	 * @param	Array private settings
	 */

	public static function useBaseSettings($data, $value = null) {
		if ( ! is_array($data) ) {
			return self::useBaseSettings(array($data => $value));
		}

		foreach ( $data as $key => $val ) {
			if ( ! isset(self::$definedGlobals[$key]) ) {
				self::$definedGlobals[$key] = $val;
			}
		}
	}

	/**
	 * baseurl
	 * 
	 * @access	public
	 * @param	String static content
	 * @return	String url
	 */

	public static function baseurl($followed = '/') {
		static $baseurl = null;

		if ( is_null($baseurl) ) {
			if ( defined('BASEURL') && ('' != BASEURL || '/' != BASEURL) ) {
				$baseurl = rtrim(BASEURL, '/');
			} else {
				$host = self::server('http_host');
				if ( $host != 'localhost' ) {
					$baseurl = (self::supportHttps() ? 'https://' : 'http://') . $host;
				} else {
					$baseurl = 'http://localhost';
				}
				$script_name = self::server('script_name');
				$base_script_filename = basename(self::server('script_filename'));
				$baseurl .= substr($script_name, 0, strpos($script_name, $base_script_filename));
				$baseurl = rtrim($baseurl, '/');
			}
		}

		$static_url = $baseurl;

		if ( ! empty($followed) && '/' != $followed ) {
			$static_url .= $followed;
		}

		return $static_url;
	}

	/**
	 * siteurl
	 * 
	 * @access	public
	 * @param	String dynamic application url
	 * @return	String application url
	 */

	public static function siteurl($followed = '/') {
		static $siteurl = null;

		if ( is_null($siteurl) ) {
			$index_page = defined('INDEX_PAGE') ? trim(INDEX_PAGE, '/') : 'index.php';
			$siteurl = rtrim(self::baseurl("/{$index_page}"), '/');
		}

		$dynamic_url = $siteurl;

		if ( ! empty($followed) && '/' != $followed ) {
			$parts = explode('?', $followed);
			$urlsuffix = self::urlsuffix();
			$dynamic_url .= (empty($urlsuffix) ? $parts[0] : rtrim($parts[0], '/').$urlsuffix);

			if ( isset($parts[1]) && ! empty($parts[1]) ) {
				$dynamic_url .= '?' . $parts[1];
			}
		}

		return $dynamic_url;
	}

	/**
	 * supportHttps
	 * 
	 * @access	public
	 * @return	Boolean
	 */

	public static function supportHttps() {
		static $https = null;

		if ( is_null($https) ) {
			if ( self::server('https', true) == 'on' || 1 == self::server('https') ) {
				$https = true;
			} elseif ( 443 == self::server('server_port') ) {
				$https = true;
			} else {
				$https = false;
			}
		}
		return $https;
	}

	/**
	 * server
	 * 
	 * @access	public
	 * @param	String name
	 * @param	Callable filter
	 * @return	Any server information
	 */

	public static function server($name, $filter = null) {
		$name = in_array($name, array('argv', 'argc'), true) ? $name : strtoupper($name);
		$value = isset($_SERVER[$name]) ? $_SERVER[$name] : null;
		return is_callable($filter) ?
			call_user_func($filter, $value) :
				$value;
	}

	/**
	 * get
	 * 
	 * @access	public
	 * @param	String name
	 * @param	Callable filter
	 * @return	Any
	 */

	public static function get($name, $filter = null) {
		if ( ! isset(self::$configs[$name]) ) {
			self::$configs[$name] = array_key_exists($name, self::$definedGlobals) ?
				self::$definedGlobals[$name] :
					self::load($name);
		}

		return is_callable($filter) ?
			call_user_func($filter, self::$configs[$name]) :
				self::$configs[$name];
	}

	/**
	 * set
	 * 
	 * @access	public
	 * @param	String name
	 * @param	Any value
	 */

	public static function set($name, $value, $overwrite = false) {
		if ( ! array_key_exists($name, self::$configs) || true === $overwrite ) {
			self::$configs[$name] = $value;
		}
	}

	/**
	 * load
	 * 
	 * @access	public
	 * @param	String name
	 * @param	Any default value
	 * @return	Any value
	 */

	public static function load($name, $default = null) {
		$config_file = realpath(self::apppath() . "/{$name}.config.php");

		if ( is_file($config_file) ) {
			$value = include $config_file;
		} else {
			$value = $default;
		}

		return $value;
	}

	/**
	 * &reff
	 * 
	 * @access	public
	 * @return	All configured values
	 */

	public static function &reff() {
		return self::$configs;
	}

}
