<?php namespace Viloveul\Utility;

/**
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Utility
 */

use Exception;
use finfo as FileInfo;
use Viloveul\Core\Events as EventManager;
use Viloveul\Core\Application;

/**
 * Example to use :
 * 
 * if ($controller->input->via('post')) :
 *     $uploader = new \Viloveul\Utility\Uploader('my_images', '/real/path/to/destination');
 *     $uploader->execute();
 *     // For Multiple success handler 
 *     $uploader->callHandler(function($data){
 *         if(! empty($data)) {
 *             print_r($data); // if ( single )
 *             print_r(func_get_args()); // if multiple
 *        }
 *     });
 *     // For Single Upload
 *     $data = $uploader->fetchDataUploaded();
 * endif;
 */

class Uploader {

	protected $destination;

	protected $errorMessages = array();

	protected $dataUploaded = array();

	protected $permittedTypes = '*';

	protected $overwrite = false;

	protected $field = 'files';

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	String field name
	 * @param	String realpath for destination
	 */

	public function __construct($field = 'files', $destination = null) {
		$this->field = $field;
		if ( is_null($destination) ) {
			$destination = Application::basepath() . '/uploads';
		}
		$this->setDestination($destination);
	}

	/**
	 * fetchDataUploaded
	 * its only can be used when data is single
	 * 
	 * @access	public
	 * @return	Array
	 */

	public function fetchDataUploaded() {
		return isset($this->dataUploaded[0]) ?
			$this->dataUploaded[0] :
				array();
	}

	/**
	 * callHandler
	 * its can be used for single or multiple upload
	 * 
	 * @access	public
	 * @param	Callable callback
	 * @return	Any
	 */

	public function callHandler($callback) {
		return call_user_func_array($callback, $this->dataUploaded);
	}

	/**
	 * execute
	 * 
	 * @access	public
	 * @return	Boolean
	 */

	public function execute() {
		if ( empty($this->field) ) {
			return false;
		}

		$files = isset($_FILES[$this->field]) ? $_FILES[$this->field] : array();

		$filelist = array();

		if ( null !== $files ) {
			foreach ( $files as $key => $val ) {
				$filelist[$key] = is_array($val) ? $val : array($val);
			}
		}

		if ( ! isset($filelist['name']) ) {
			return false;
		}

		$offset = count($filelist['name']);

		for ( $i = 0; $i < $offset; $i++ ) {
			$this->process(
				$filelist['name'][$i],
				$filelist['type'][$i],
				$filelist['tmp_name'][$i],
				$filelist['size'][$i],
				$filelist['error'][$i]
			);
		}

		return (count($this->dataUploaded) > 0);
	}

	/**
	 * overwrite
	 * 
	 * @access	public
	 * @param	Boolean value
	 * @return	void
	 */

	public function overwrite($value) {
		if ( is_bool($value) ) {
			$this->overwrite = $value;
		}
		return $this;
	}

	/**
	 * setDestination
	 * 
	 * @access	public
	 * @param	String value
	 * @return	void
	 */

	public function setDestination($destination) {
		if ( $realpath = realpath($destination) ) {
			$this->destination = rtrim($destination, '/');
		}
		return $this;
	}

	/**
	 * getDestination
	 * 
	 * @access	public
	 * @return	String current destination path
	 */

	public function getDestination() {
		if ( empty($this->destination) or ! is_dir($this->destination) ) {
			throw new Exception('Destination path does not exists');
		}
		return $this->destination;
	}

	/**
	 * setPermittedType
	 * 
	 * @access	public
	 * @param	[mixed] type
	 * @return	void
	 */

	public function setPermittedType($value) {
		$this->permittedTypes = is_string($value) ?
			implode('|', func_get_args()) :
				implode('|', (array) $value);
		return $this;
	}

	/**
	 * getPermittedType
	 * 
	 * @access	public
	 * @return	String
	 */

	public function getPermittedType() {
		return empty($this->permittedTypes) ? '*' : $this->permittedTypes;
	}

	/**
	 * hasError
	 * 
	 * @access	public
	 * @return	Boolean
	 */

	public function hasError() {
		return (count($this->errorMessages) > 0);
	}

	/**
	 * displayErrors
	 * 
	 * @access	public
	 * @param	String prefix
	 * @param	String suffix
	 * @return	null
	 */

	public function displayErrors($prefix = '', $suffix = '') {
		$messages = array_map(
			function($message) use($prefix, $suffix) {
				return $prefix.$message.$suffix;
			},
			$this->errorMessages
		);
		echo implode("\n", $messages);
		return null;
	}

	/**
	 * isAllowed
	 * 
	 * @access	protected
	 * @param	String extension or type
	 * @return	Boolean
	 */

	protected function isAllowed($ext) {
		if ( '*' == $this->getPermittedType() ) {
			return true;
		}
		$allowedTypes = explode('|', $this->getPermittedType());

		return (boolean) in_array($ext, $allowedTypes, true);
	}

	/**
	 * isTrueImage
	 * 
	 * @access	protected
	 * @param	String source file
	 * @param	&String width
	 * @param	&String height
	 * @return	Boolean
	 */

	protected function isTrueImage($source, &$width = 0, &$height = 0) {
		$check = @getimagesize($source);
		if ( $check !== false ) {
			$width = $check[0];
			$height = $check[1];
			return true;
		}
		return false;
	}

	/**
	 * detectMimeType
	 * 
	 * @access	protected
	 * @param	String source file
	 * @param	String data client (mime_type)
	 */

	protected function detectMimeType($source, $dataClient) {
		$mime = $dataClient;
		if ( class_exists('FileInfo') ) {
			$fileInfo = new FileInfo(FILEINFO_MIME);
			$detect = $fileInfo->file($source);
			if ( preg_match('/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/', $detect, $matches) ) {
				$mime = $matches[1];
			}
		} elseif ( @function_exists('mime_content_type') ) {
			$mime = @mime_content_type($source);
		}
		return $mime;
	}

	/**
	 * detectContentType
	 * 
	 * @access	protected
	 * @param	String source file
	 * @return	String content type
	 */

	protected function detectContentType($mime) {
		$parts = explode('/', $mime);
		return in_array($parts[0], array('audio', 'video', 'image', 'application', 'text'), true) ?
			$parts[0] :
				'unknown';
	}

	/**
	 * parseFilename
	 * 
	 * @access	protected
	 * @param	String client name
	 * @param	String &Extention
	 * @return	String filename
	 */

	protected function parseFilename($clientName, &$ext = '.txt') {
		$fakename = trim(strtolower($clientName), '.');
		$_ext = explode('.', $fakename);

		if ( ($countExt = count($_ext)) > 1 ) {
			if ( empty($_ext[0]) ) {
				$_name = $_ext[1];
			} else {
				$ext = '.' . array_pop($_ext);
				$_name = implode('-', $_ext);
			}
		} else {
			$_name = $fakename;
		}

		return trim(preg_replace('/([^a-z0-9]+)/', '-', $_name), '-');
	}

	/**
	 * process
	 * 
	 * @access	protected
	 * @param	String filename
	 * @param	String mime type
	 * @param	String tmp uploaded path
	 * @param	Int size
	 * @param	Int error
	 */

	protected function process($client_name, $client_mime, $tmp_name, $file_size, $upload_error) {
		if ( ! empty($upload_error) ) {
			$this->errorMessages[] = sprintf('<i>%s</i> : have error with message "%s".', $client_name, $upload_error);
			return false;
		}

		$basename = $this->parseFilename($client_name, $extention);

		if ( true !== $this->isAllowed(substr($extention, 1)) ) {
			$this->errorMessages[] = sprintf('<i>%s</i> : type is not allowed', $client_name);
			return false;
		}

		$is_image = $this->isTrueImage($tmp_name, $image_width, $image_height);

		$mime_type = $this->detectMimeType($tmp_name, $client_mime);

		$content_type = $this->detectContentType($mime_type);

		$directory = date('Y-m-d');

		$uploaded = $directory . ' ' . date('H:i:s');

		$target_path = $this->getDestination() . '/' . $directory . '/';

		is_dir($target_path) or @mkdir($target_path, 0777, true);

		if ( false === $this->overwrite ) {

			$copy = 1;

			$filename = $basename.$extention;

			while (file_exists($target_path.$filename)) {
				$filename = $basename . '-' . (++$copy) . $extention;
			}
		}

		// get action

		try {
			move_uploaded_file($tmp_name, $target_path.$filename);

			$data = compact(
				'client_name',
				'basename',
				'filename',
				'uploaded',
				'directory',
				'mime_type',
				'file_size',
				'extention',
				'content_type',
				'is_image',
				'image_width',
				'image_height'
			);
			$data['target_path'] = realpath($target_path);
			$data['realpath'] = realpath($target_path.$filename);

			$this->dataUploaded[] = $data;

		} catch (Exception $e) {
			$this->errorMessages[] = sprintf('<i>%s</i> : %s', $client_name, $e->getMessage());
		}

	}

}
