<?php namespace Viloveul\Utility;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

use Viloveul\Core\Configure;

class AssetManager {

	protected static $loadedSources = array();

	protected static $registeredSources = array();

	/**
	 * load
	 * 
	 * @access	public
	 * @param	String type
	 * @param	String id
	 * @param	String source target
	 * @return	Boolean
	 */

	public static function load($type, $id, $source) {
		$key = "{$id}-{$type}";

		// make sure the source is only printed one at once time

		if ( ! self::confirmLoaded($key, true) ) {
			return null;
		}

		$format = ($type == 'css') ?
			'<link rel="stylesheet" type="text/css" id="%s" href="%s" />':
				'<script type="text/javascript" id="%s" src="%s"></script>';

		printf("{$format}\n", $key, sprintf($source, rtrim(Configure::baseurl(), '/')));

		return true;
	}

	/**
	 * registerSource
	 * 
	 * @access	public
	 * @param	String id
	 * @param	String source
	 * @param	[mixed] String type
	 * @return	Boolean
	 */

	public static function registerSource($id, $source, $type = null) {
		if ( is_null($type) ) {
			if ( preg_match('#\.(css|js)$#', $source, $matches) ) {
				$type = $matches[1];
			}
		}

		if ( in_array($type, array('css', 'js'), true) ) {
			self::$registeredSources["{$id}-{$type}"] = $source;
			return true;
		}

		return false;
	}

	/**
	 * printStyle
	 * 
	 * @access	public
	 * @param	String source id
	 * @param	String|Array dependency(s)
	 * @return	Boolean
	 */

	public static function printStyle($id, $dependency = null) {
		return self::printScript($id, $dependency, 'css');
	}

	/**
	 * printScript
	 * 
	 * @access	public
	 * @param	String source id
	 * @param	String|Array dependency(s)
	 * @param	String type
	 * @return	Boolean
	 */

	public static function printScript($id, $dependency = null, $type = 'js') {
		if ( is_null($dependency) ) {
			$dependencies = array();
		} else {
			$dependencies = is_string($dependency) ? array($dependency) : (array) $dependency;
		}

		$key = "{$id}-{$type}";

		if ( ! isset(self::$registeredSources[$key]) ) {
			return null;
		} elseif ( ! self::confirmLoaded($key, true) ) {
			return false;
			
		}

		if ( ! empty($dependencies) ) {
			foreach ( $dependencies as $dependency_id ) {
				self::printScript($dependency_id, null, $type);
			}
		}

		$format = ($type == 'css') ?
			'<link rel="stylesheet" type="text/css" id="%s" href="%s" />':
				'<script type="text/javascript" id="%s" src="%s"></script>';

		printf(
			"{$format}\n",
			$key,
			sprintf(
				self::$registeredSources[$key],
				rtrim(Configure::baseurl(), '/')
			)
		);

		return true;
	}

	/**
	 * confirmLoaded
	 * 
	 * @access	protected
	 * @param	String key source
	 * @param	Boolean autopush when not loaded
	 * @return	Boolean
	 */

	protected static function confirmLoaded($key, $autopush = false) {
		if ( in_array($key, self::$loadedSources, false) ) {
			return false;
		} elseif ( true === $autopush ) {
			array_push(self::$loadedSources, $key);
		}

		return true;
	}

}
