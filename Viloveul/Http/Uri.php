<?php namespace Viloveul\Http;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Http
 */

use Viloveul\Core\Configure;

class Uri {

	protected $segments = array();

	/**
	 * Constructor
	 * 
	 * @access	public
	 */

	public function __construct($request = '/') {
		$this->segments = array_filter(explode('/', $request), 'trim');
		array_unshift($this->segments, null);
		unset($this->segments[0]);
	}

	/**
	 * segment
	 * 
	 * @access	public
	 * @param	Int index of segments
	 * @return	Any or null
	 */

	public function segment($index) {
		return isset($this->segments[$index]) ? $this->segments[$index] : null;
	}

	/**
	 * baseurl
	 * its aliases for \Viloveul\Core\Configure::baseurl()
	 * 
	 * @access	public
	 * @param	String static content
	 * @return	String baseurl
	 */

	public function baseurl($content = null) {
		return Configure::baseurl($content);
	}

	/**
	 * siteurl
	 * its aliases for \Viloveul\Core\Configure::siteurl()
	 * 
	 * @access	public
	 * @param	String application content
	 * @return	String siteurl
	 */

	public function siteurl($content = null) {
		return Configure::siteurl($content);
	}

	/**
	 * currenturl
	 * its aliases for \Viloveul\Core\Request::currenturl()
	 * 
	 * @access	public
	 * @return	String current url
	 */

	public function currenturl() {
		return Request::currenturl();
	}

}
