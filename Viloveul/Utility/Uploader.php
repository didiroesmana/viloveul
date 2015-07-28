<?php namespace Viloveul\Utility;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Utility
 */

use Viloveul\Core\Events as EventManager;
use Viloveul\Core\Configure;
use Viloveul\Http\Request;
use Exception;
use finfo as FileInfo;

/**
 * Example to use :
 * 
 * if ($controller->input->via('post')) :
 * 		$uploader = new \Viloveul\Utility\Uploader('my_images');
 * 		if ($uploader->execute() !== false) {
 * 			$uploader->runHandler(function($data){
 * 				print_r($data); // if ( single )
 * 				print_r(func_get_args()); // if multiple
 * 			});
 * 		}
 * endif;
 */

class Uploader {

	private $directorySeparator = '/';

	protected $basepath = '';

	protected $errorMessages = array();

	protected $dataUploaded = array();

	protected $destination = 'contents/uploads';

	protected $permittedTypes = '*';

	protected $overwrite = false;

	protected $field = 'files';

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	String field name
	 */

	public function __construct($field = 'files') {
		$this->directorySeparator = defined('DIRECTORY_SEPARATOR') ? DIRECTORY_SEPARATOR : '/';
		$this->basepath = Configure::getBaseDirectory();
	}

	/**
	 * fetchDataUploaded
	 * its only can be used when data is single
	 * 
	 * @access	public
	 */

	public function fetchDataUploaded() {
		if ( isset($this->dataUploaded[0]) ) {
			return $this->dataUploaded[0];
		}
	}

	/**
	 * runHandler
	 * its can be used for single or multiple upload
	 * 
	 * @access	public
	 * @param	Callable callback
	 * @return	Any
	 */

	public function runHandler($callback) {
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

		$files = Request::input('file', $this->field, array());

		$listFiles = array();

		if ( null !== $files ) {
			foreach ( $files as $key => $val ) {
				$listFiles[$key] = is_array($val) ? $val : array($val);
			}
		}

		if ( ! isset($listFiles['name']) ) {
			return false;
		}

		$offset = count($listFiles['name']);
		for ( $i = 0; $i < $offset; $i++ ) {
			$this->process(
				$listFiles['name'][$i],
				$listFiles['type'][$i],
				$listFiles['tmp_name'][$i],
				$listFiles['size'][$i],
				$listFiles['error'][$i]
			);
		}

		if ( $this->hasError() ) {
			EventManager::addListener('upload_error', array($this, 'displayErrors'));
		}

		if ( count($this->dataUploaded) < 1 ) {
			return false;
		}

		return true;
	}

	/**
	 * overwrite
	 * 
	 * @access	public
	 * @param	Boolean value
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
	 */

	public function setDestination($destination) {
		$this->destination = $destination;
		return $this;
	}

	/**
	 * setPermittedTypes
	 * 
	 * @access	public
	 * @param	[mixed] type
	 */

	public function setPermittedTypes($type, $multipleType = null) {
		$arrayType = is_null($multipleType) ? $type : func_get_args();
		$this->permittedTypes = is_array($arrayType) ? implode('|', array_filter($arrayType, 'trim')) : $arrayType;
		return $this;
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
	 * getPermittedTypes
	 * 
	 * @access	public
	 * @return	String
	 */

	public function getPermittedTypes() {
		return empty($this->permittedTypes) ? '*' : $this->permittedTypes;
	}

	/**
	 * getDestination
	 * 
	 * @access	public
	 * @return	String current destination path
	 */

	public function getDestination() {
		return $this->destination;
	}

	/**
	 * isAllowed
	 * 
	 * @access	protected
	 * @param	String extension or type
	 * @return	Boolean
	 */

	protected function isAllowed($ext) {
		if ( '*' == $this->getPermittedTypes() ) {
			return true;
		}
		$allowedTypes = explode('|', $this->getPermittedTypes());
		if ( in_array($ext, $allowedTypes, true) ) {
			return true;
		}
		return false;
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

	protected function process($name, $mime, $tmp, $size, $error) {
		if ( ! empty($error) ) {
			$this->errorMessages[] = sprintf('<i>%s</i> : have error with message "%s".', $name, $error);
			return false;
		}

		$fakename = trim(strtolower($name), '.');
		$ext = '.txt';
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
		$basename = trim(preg_replace('/([^a-z0-9]+)/', '-', $_name), '-');

		if ( true !== $this->isAllowed(substr($ext, 1)) ) {
			$this->errorMessages[] = sprintf('<i>%s</i> : type is not allowed', $name);
			return false;
		}

		$isImage = in_array($ext, array('.gif', '.jpg', '.jpeg', '.jpe', '.png'), true);
		$imageWidth = 0;
		$imageHeight = 0;
		$_imageSize = @getimagesize($tmp);

		if ( $isImage && false === $_imageSize ) {
			$this->errorMessages[] = sprintf('<i>%s</i> : type is not true image, but uploaded as image', $name);
			return false;
		} elseif ( false !== $_imageSize ) {
			$imageWidth = $_imageSize[0];
			$imageHeight = $_imageSize[1];
		}

		if ( class_exists('FileInfo', false) ) {
			$fi = new FileInfo(FILEINFO_MIME);
			$_mime = $fi->file($tmp);
			if ( preg_match('/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/', $_mime, $mimeMatches) ) {
				$mime = $mimeMatches[1];
			}
		} elseif ( function_exists('mime_content_type') ) {
			$mime = @mime_content_type($tmp);
		}

		$_type = explode('/', $mime);

		if ( isset($_type[0]) && in_array($_type[0], array('audio', 'video', 'image', 'application', 'text'), true) ) {
			$type = $_type[0];
		} else {
			$type = 'unknown';
		}

		// get act

		$timestamp = time();
		$uploaded = date('Y-m-d H:i:s', $timestamp);
		$directory = date('Y-m-d', $timestamp);
		$path = trim(trim($this->destination, '/') . '/' . $directory, '/');
		$target = str_replace('/', $this->directorySeparator, rtrim($this->basepath.'/'.$path, '/'));
		is_dir($target) or @mkdir($target, 0777, true);
		$filename = $basename.$ext;

		if ( false === $this->overwrite ) {
			$loop4check = true;
			$i = 1;

			do {
				$loop4check = false;
				if ( file_exists($target.$this->directorySeparator.$filename) ) {
					$i++;
					$loop4check = true;
					$filename = $basename.'_'.$i.$ext;
					clearstatcache();
				}

			} while ( false !== $loop4check );
		}

		try {
			move_uploaded_file($tmp, $target.$this->directorySeparator.$filename);
			$this->dataUploaded[] = array(
				'oriname' => $name,
				'basename' => $basename,
				'fullpath' => realpath($target.$this->directorySeparator.$filename),
				'file_name' => $filename,
				'file_uploaded' => $uploaded,
				'file_timestamp' => $timestamp,
				'file_type' => $type,
				'file_path' => $path,
				'file_mime' => $mime,
				'file_size' => $size,
				'file_ext' => $ext,
				'is_image' => $isImage,
				'image_width' => $imageWidth,
				'image_height' => $imageHeight
			);

		} catch ( Exception $e ) {
			$this->errorMessages[] = sprintf('<i>%s</i> : %s', $name, $e->getMessage());
		}

	}

}
