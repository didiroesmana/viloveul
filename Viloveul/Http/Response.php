<?php namespace Viloveul\Http;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Http
 */

use Viloveul\Core\Configure;
use Viloveul\Core\View;

class Response {

	protected $output = '';

	protected $contentType = 'text/html';

	protected $headers = array();

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @return	void
	 */

	public function __construct() {
	}

	/**
	 * clear
	 * 
	 * @access	public
	 * @return	void
	 */

	public function clear() {
		$this->output = '';
		$this->contentType = 'text/html';
		$this->headers = array();

		if ( $lvl = ob_get_level() ) {
			for ( $i = $lvl; $i > 0; $i-- ) {
				ob_flush();
			}
		}

		return $this;
	}

	/**
	 * header
	 * 
	 * @access	public
	 * @param	String header
	 * @param	Boolean
	 * @return	void
	 */

	public function httpHeader($header, $overwrite = true) {
		$this->headers[] = array($header, $overwrite);
		return $this;
	}

	/**
	 * setContentType
	 * 
	 * @access	public
	 * @param	String content_type
	 * @return	void
	 */

	public function setContentType($contentType) {
		$this->contentType = $contentType;
		return $this;
	}

	/**
	 * getContentType
	 * 
	 * @access	public
	 * @return	String content_type
	 */

	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * setOutput
	 * 
	 * @access	public
	 * @param	String data
	 * @param	Boolean
	 * @return	void
	 */

	public function setOutput($data, $apppend = false) {
		$output = ($data instanceof View) ? $data->render() : ((string) $data);

		$this->output = (true === $apppend) ?
			($this->output.$output) :
				$output;

		return $this;
	}

	/**
	 * getOutput
	 * 
	 * @access	public
	 * @return	String output
	 */

	public function getOutput() {
		return $this->output;
	}

	/**
	 * send
	 * 
	 * @access	public
	 * @param	String output if any
	 * @return	void
	 */

	public function send($data = null) {
		is_null($data) or $this->setOutput($data, true);

		if ( ! headers_sent() ) {

			$headers = array_map(
				'unserialize',
				array_unique(
					array_map('serialize', $this->headers)
				)
			);

			foreach ( $headers as $header ) {
				header($header[0], $header[1]);
			}

			@header('Content-Type: ' . $this->contentType, true);
		}

		$output = $this->getOutput();

		$this->clear();

		print $output;
	}

	/**
	 * redirect
	 * 
	 * @access	public
	 * @param	String dynamic/static url
	 * @return	String fixed url
	 */

	public static function redirect($target) {
		$url = !preg_match('#^\w+\:\/\/#', $target) ?
			Configure::siteurl($target) :
				$target;

		if ( ! headers_sent() ) {
			header("Location: {$url}");
			exit();
		}

		printf('<script type="text/javascript">window.location.href = "%s";</script>', $url);
	}

}
