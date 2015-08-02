<?php namespace Viloveul\Utility;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

use Viloveul\Core\Configure;

class AssetManager {

	protected static $loadedScripts = array();

	/**
	 * load
	 * 
	 * @access	public
	 * @param	String type
	 * @param	String id
	 * @param	String source target
	 */

	public static function load($type, $id, $source) {
		$key = "{$id}-{$type}";

		// make sure the source is only printed one at once time

		if ( in_array($key, self::$loadedScripts) ) {
			return null;
		}

		array_push(self::$loadedScripts, $key);

		$format = ($type == 'css') ?
			'<link rel="stylesheet" type="text/css" id="%s" href="%s" />':
				'<script type="text/javascript" id="%s" src="%s"></script>';
		printf("{$format}\n", $key, sprintf($source, rtrim(Configure::baseurl(), '/')));
	}

}
