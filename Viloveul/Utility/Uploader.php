<?php

namespace Viloveul\Utility;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Exception;
use finfo as FileInfo;

/**
 * Example to use :.
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
class Uploader
{
    /**
     * @var array
     */
    protected $dataUploaded = [];

    /**
     * @var mixed
     */
    protected $destination;

    /**
     * @var array
     */
    protected $errorMessages = [];

    /**
     * @var string
     */
    protected $field = 'files';

    /**
     * @var mixed
     */
    protected $overwrite = false;

    /**
     * @var string
     */
    protected $permittedTypes = '*';

    /**
     * @param $field
     * @param $destination
     */
    public function __construct($field = 'files', $destination = '/uploads')
    {
        $this->field = $field;
        $this->setDestination($destination);
    }

    /**
     * @param $callback
     */
    public function callHandler($callback)
    {
        return call_user_func_array($callback, $this->dataUploaded);
    }

    /**
     * @param  $prefix
     * @param  $suffix
     * @return null
     */
    public function displayErrors($prefix = '', $suffix = '')
    {
        $messages = array_map(
            function ($message) use ($prefix, $suffix) {
                return $prefix . $message . $suffix;
            },
            $this->errorMessages
        );
        echo implode("\n", $messages);

        return;
    }

    public function execute()
    {
        if (empty($this->field)) {
            return false;
        }

        $files = isset($_FILES[$this->field]) ? $_FILES[$this->field] : [];

        $filelist = [];

        if (null !== $files) {
            foreach ($files as $key => $val) {
                $filelist[$key] = is_array($val) ? $val : [$val];
            }
        }

        if (!isset($filelist['name'])) {
            return false;
        }

        $offset = count($filelist['name']);

        for ($i = 0; $i < $offset; ++$i) {
            $this->process(
                $filelist['name'][$i],
                $filelist['type'][$i],
                $filelist['tmp_name'][$i],
                $filelist['size'][$i],
                $filelist['error'][$i]
            );
        }

        return count($this->dataUploaded) > 0;
    }

    public function fetchDataUploaded()
    {
        return isset($this->dataUploaded[0]) ? $this->dataUploaded[0] : [];
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        if (empty($this->destination) or !is_dir($this->destination)) {
            throw new Exception('Destination path does not exists');
        }

        return $this->destination;
    }

    public function getPermittedType()
    {
        return empty($this->permittedTypes) ? '*' : $this->permittedTypes;
    }

    public function hasError()
    {
        return count($this->errorMessages) > 0;
    }

    /**
     * @param  $value
     * @return mixed
     */
    public function overwrite($value)
    {
        if (is_bool($value)) {
            $this->overwrite = $value;
        }

        return $this;
    }

    /**
     * @param  $destination
     * @return mixed
     */
    public function setDestination($destination)
    {
        if ($realpath = realpath($destination)) {
            $this->destination = rtrim($destination, '/');
        }

        return $this;
    }

    /**
     * @param  $value
     * @return mixed
     */
    public function setPermittedType($value)
    {
        $this->permittedTypes = is_string($value) ? implode('|', func_get_args()) : implode('|', (array) $value);

        return $this;
    }

    /**
     * @param $mime
     */
    protected function detectContentType($mime)
    {
        $parts = explode('/', $mime);

        return in_array($parts[0], array('audio', 'video', 'image', 'application', 'text'), true) ? $parts[0] : 'unknown';
    }

    /**
     * @param  $source
     * @param  $dataClient
     * @return mixed
     */
    protected function detectMimeType($source, $dataClient)
    {
        $mime = $dataClient;
        if (class_exists('FileInfo')) {
            $fileInfo = new FileInfo(FILEINFO_MIME);
            $detect = $fileInfo->file($source);
            if (preg_match('/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/', $detect, $matches)) {
                $mime = $matches[1];
            }
        } elseif (@function_exists('mime_content_type')) {
            $mime = @mime_content_type($source);
        }

        return $mime;
    }

    /**
     * @param $ext
     */
    protected function isAllowed($ext)
    {
        if ('*' == $this->getPermittedType()) {
            return true;
        }
        $allowedTypes = explode('|', $this->getPermittedType());

        return (boolean) in_array($ext, $allowedTypes, true);
    }

    /**
     * @param $source
     * @param $width
     * @param $height
     */
    protected function isTrueImage($source, &$width = 0, &$height = 0)
    {
        $check = @getimagesize($source);
        if ($check !== false) {
            $width = $check[0];
            $height = $check[1];

            return true;
        }

        return false;
    }

    /**
     * @param $clientName
     * @param $ext
     */
    protected function parseFilename($clientName, &$ext = '.txt')
    {
        $fakename = trim(strtolower($clientName), '.');
        $_ext = explode('.', $fakename);

        if (($countExt = count($_ext)) > 1) {
            if (empty($_ext[0])) {
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
     * @param $client_name
     * @param $client_mime
     * @param $tmp_name
     * @param $file_size
     * @param $upload_error
     */
    protected function process($client_name, $client_mime, $tmp_name, $file_size, $upload_error)
    {
        if (!empty($upload_error)) {
            $this->errorMessages[] = sprintf('<i>%s</i> : have error with message "%s".', $client_name, $upload_error);

            return false;
        }

        $basename = $this->parseFilename($client_name, $extention);

        if (true !== $this->isAllowed(substr($extention, 1))) {
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

        if (false === $this->overwrite) {
            $copy = 1;

            $filename = $basename . $extention;

            while (file_exists($target_path . $filename)) {
                $filename = $basename . '-' . (++$copy) . $extention;
            }
        }

        // get action

        try {
            move_uploaded_file($tmp_name, $target_path . $filename);

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
            $data['realpath'] = realpath($target_path . $filename);

            $this->dataUploaded[] = $data;
        } catch (Exception $e) {
            $this->errorMessages[] = sprintf('<i>%s</i> : %s', $client_name, $e->getMessage());
        }
    }
}
