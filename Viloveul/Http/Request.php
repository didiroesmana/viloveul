<?php

namespace Viloveul\Http;

/*
 * @author Fajrul Akbar Zuhdi
 * @email fajrulaz@gmail.com
 */

use Viloveul\Core\Configure;

class Request
{
    /**
     * @var mixed
     */
    protected static $globalRequest = null;

    public static function createFromGlobals()
    {
        is_null(self::$globalRequest) and self::resolveGlobalRequest();

        return self::$globalRequest;
    }

    public static function currenturl()
    {
        $uriString = self::parseRequestUri();
        $query = Configure::server('query_string');

        return empty($query) ? Configure::siteurl($uriString) : Configure::siteurl("/{$uriString}?{$query}");
    }

    /**
     * @param $default
     */
    public static function httpReferer($default = '')
    {
        return Configure::server('http_referer', function ($value) use ($default) {
            return is_null($value) ? $default : $value;
        });
    }

    public static function isAjax()
    {
        return Configure::server('http_x_requested_with', 'strtolower') == 'xmlhttprequest';
    }

    public static function isCli()
    {
        if (!defined('PHP_SAPI')) {
            return false;
        }

        return PHP_SAPI == 'cli';
    }

    /**
     * @param $option
     */
    public static function isMethod($option)
    {
        if (in_array($option, array('put', 'patch', 'delete', 'options'))) {
            return isset($_POST['_METHOD']) && strtolower($_POST['_METHOD']) == $option;
        }

        return Configure::server('request_method', 'strtolower') == $option;
    }

    public static function resolveGlobalRequest()
    {
        self::$globalRequest = self::isCli() ? self::parseCommandLine() : self::parseRequestUri();
    }

    /**
     * @return mixed
     */
    protected static function parseCommandLine()
    {
        /**
         * @var mixed
         */
        static $request = null;

        if (is_null($request)) {
            $request = '/';
            if (!isset($_SERVER['argv'])) {
                return $request;
            }

            $path = isset($_SERVER['argv'][1]) ? '/' . trim($_SERVER['argv'][1], '/') : '/';
            $query = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : '';

            $request = empty($path) ? '/' : $path;

            $_SERVER['QUERY_STRING'] = $query;
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }

        return $request;
    }

    /**
     * @return mixed
     */
    protected static function parseRequestUri()
    {
        /**
         * @var mixed
         */
        static $request = null;

        if (is_null($request)) {
            $request = '/';

            if (!isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
                return $request;
            }

            $parts = parse_url($_SERVER['REQUEST_URI']);

            $path = isset($parts['path']) ? $parts['path'] : '/';
            $query = isset($parts['query']) ? $parts['query'] : '';
            $script = $_SERVER['SCRIPT_NAME'];

            if (0 === strpos($path, $script)) {
                $path = substr($path, strlen($script));
            } else {
                $dirname = dirname($script);
                if (0 === strpos($path, $dirname)) {
                    $path = substr($path, strlen($dirname));
                }
            }

            $request = empty($path) ? '/' : $path;

            $_SERVER['QUERY_STRING'] = $query;
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        }

        return $request;
    }
}
