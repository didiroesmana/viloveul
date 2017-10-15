<?php

namespace Viloveul\Core;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Exception;

class View
{
    /**
     * @var mixed
     */
    protected $directory = null;

    /**
     * @var mixed
     */
    protected $filename = false;

    /**
     * @var array
     */
    protected $vars = array();

    /**
     * @var array
     */
    private static $data = array();

    /**
     * @param $filename
     * @param array       $vars
     */
    public function __construct($filename, array $vars = array())
    {
        $this->filename = $filename;

        is_array($vars) && $this->set($vars);
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param  $var
     * @param  $defaultValue
     * @return mixed
     */
    public function get($var, $defaultValue = null)
    {
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$var];
        } elseif (array_key_exists($name, static::$data)) {
            return static::$data[$var];
        }

        return $defaultValue;
    }

    /**
     * @param $name
     * @param $defaultValue
     */
    public static function globalDataGet($name, $defaultValue = null)
    {
        return array_key_exists($name, static::$data) ? static::$data[$name] : $defaultValue;
    }

    /**
     * @param $data
     * @param $value
     */
    public static function globalDataSet($data, $value = null)
    {
        if (is_string($data)) {
            return static::globalDataSet(array($data => $value));
        }

        foreach ((array) $data as $var => $val) {
            if (is_null($val) && array_key_exists($var, static::$data)) {
                unset(static::$data[$var]);
            } else {
                static::$data[$var] = $val;
            }
        }
    }

    /**
     * @param $name
     * @param array   $data
     */
    public static function make($name, array $data = array())
    {
        static::globalDataSet($data, null);

        return new static($name);
    }

    /**
     * @param $__local281291callbackFilter
     */
    public function render($__local281291callbackFilter = null)
    {
        $this->directory = Configure::read('directory') . '/Views';

        $__local281291vars = array_merge(static::$data, $this->vars);
        $__local281291fileparts = array_filter(explode('/', $this->filename), 'trim');
        $__local281291filename = $this->directory . '/' . implode('/', $__local281291fileparts) . '.php';

        if (!is_file($__local281291filename)) {
            throw new Exception('Unable to locate view : ' . $__local281291filename);
        }

        $__local281291contentFile = $this->loadContentFile($__local281291filename);

        extract($__local281291vars);

        ob_start();

        eval('?>' . $__local281291contentFile);

        $__local281291outputRendering = ob_get_clean();

        $__local281291trimOutputRendering = trim($__local281291outputRendering);

        if (is_callable($__local281291callbackFilter)) {
            return call_user_func($__local281291callbackFilter, $__local281291trimOutputRendering, $this);
        }
        return $__local281291trimOutputRendering;
    }

    /**
     * @param  $var
     * @param  $value
     * @return mixed
     */
    public function set($var, $value = null)
    {
        if (is_string($var)) {
            return $this->set(array($var => $value));
        }

        foreach ((array) $var as $key => $val) {
            $this->vars[$key] = $val;
        }

        return $this;
    }

    /**
     * @param  $contents
     * @return mixed
     */
    protected function filterLoadedContents($contents = '')
    {
        if (strpos($contents, '{{@') !== false && false !== strpos($contents, '}}')) {
            $contents = preg_replace_callback(
                '#\{\{\@(.+)\}\}#U',
                array($this, 'handleContentFiltered'),
                $contents
            );
        }

        return $contents;
    }

    /**
     * @param $matches
     */
    protected function handleContentFiltered($matches)
    {
        $filename = trim($matches[1]);
        $path = "{$this->directory}/{$filename}.php";

        return is_file($path) ? $this->loadContentFile($path) : $matches[0];
    }

    /**
     * @param  $filename
     * @return mixed
     */
    protected function loadContentFile($filename)
    {
        return $this->filterLoadedContents(
            file_get_contents($filename)
        );
    }
}
