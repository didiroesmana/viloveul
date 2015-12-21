<?php namespace Viloveul\Http;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Http
 */

class Input {

	protected $streams = null;

	protected $headers = null;

	/**
	 * get
	 * 
	 * @access	public
	 * @param	String name
	 * @param	[mixed]
	 * @return	[mixed]
	 */

	public function get($name, $default = null) {
		return array_key_exists($name, $_GET) ? $_GET[$name] : $default;
	}

	/**
	 * post
	 * 
	 * @access	public
	 * @param	String name
	 * @param	[mixed]
	 * @return	[mixed]
	 */

	public function post($name, $default = null) {
		return array_key_exists($name, $_POST) ? $_POST[$name] : $default;
	}

	/**
	 * file
	 * 
	 * @access	public
	 * @param	String name
	 * @param	Array
	 * @return	Array
	 */

	public function file($name, $default = array()) {
		return isset($_FILES[$name]) ? $_FILES[$name] : $default;
	}

	/**
	 * stream
	 * 
	 * @access	public
	 * @param	String
	 * @param	[mixed]
	 * @return	[mixed]
	 */

	public function stream($name, $default = null) {
		if ( null === $this->streams ) {
			parse_str(file_get_contents('php://input'), $this->streams);
			is_array($this->streams) or ($this->streams = array());
		}
		return array_key_exists($name, $this->streams) ? $this->streams[$name] : $default;
	}

	/**
	 * header
	 * 
	 * @access	public
	 * @param	String
	 * @param	[mixed]
	 * @return	[mixed]
	 */

	public function header($name, $default = null) {
		if ( null === $this->headers ) {
			if ( function_exists('apache_request_headers') ) {
				$this->headers = apache_request_headers();
			} elseif ( function_exists('getallheaders') ) {
				$this->headers = getallheaders();
			} else {
				$this->headers = array();

				$this->headers['Content-Type'] = Configure::server('content_type', function($val){
					return is_null($val) ? @getenv('CONTENT_TYPE') : $val;
				});

				foreach ($_SERVER as $key => $val) {
					if (sscanf($key, 'HTTP_%s', $header) === 1) {
						$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower($header))));
						$this->headers[$header] = $this->_fetch_from_array($_SERVER, $key, $xss_clean);
					}
				}
			}
		}
		return isset($this->headers[$name]) ? $this->headers[$name] : $default;
	}

	/**
	 * via
	 * 
	 * @access	public
	 * @param	String
	 * @return	Boolean
	 */

	public function via($method) {
		return Request::method('strtolower') == strtolower($method);
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

}
