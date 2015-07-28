<?php namespace Viloveul\Http;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Http
 */

use Viloveul\Core\Configure;

class Input {

	/**
	 * get
	 * 
	 * @access	public
	 * @param	String name
	 * @param	String|Array default value
	 * @return	Any
	 */

	public function get($name = null, $defaultValue = null) {
		return is_null($name) ? $_GET : Request::input('get', $name, $defaultValue);
	}

	/**
	 * post
	 * 
	 * @access	public
	 * @param	String name
	 * @param	String|Array default value
	 * @return	Any
	 */

	public function post($name = null, $defaultValue = null) {
		return is_null($name) ? $_POST : Request::input('post', $name, $defaultValue);
	}

	/**
	 * file
	 * 
	 * @access	public
	 * @param	String name
	 * @param	Array default value
	 * @return	Any
	 */

	public function file($name = null, array $defaultValue = array()) {
		return is_null($name) ? $_FILES : Request::input('file', $name, $defaultValue);
	}

	/**
	 * via
	 * 
	 * @access	public
	 * @param	String
	 * @return	String request method
	 */

	public function via($request = null) {
		return is_null($request) ? Configure::server('request_method', 'strtolower') : Request::isMethod($request);
	}

	/**
	 * ipAddress
	 * 
	 * @access	public
	 * @return	String ip address
	 */

	public function ipAddress() {
		return Configure::server('remote_addr');
	}

	/**
	 * referer
	 * 
	 * @access	public
	 * @param	String default value
	 * @return	String url referer
	 */

	public function referer($default = '') {
		return Request::httpReferer($default);
	}

}
