<?php

namespace Viloveul\Http;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Viloveul\Core\Configure;

class Request
{
    /**
     * @var mixed
     */
    protected static $globalRequest = null;

    /**
     * @var mixed
     */
    protected $uri;

    /**
     * @param Uri $uri
     */
    public function __construct(Uri $uri)
    {
        $this->uri = $uri;
    }

    public function createFromGlobals()
    {
        is_null(self::$globalRequest) and $this->resolveGlobalRequest();

        return self::$globalRequest;
    }

    public function isAjax()
    {
        return Configure::server('http_x_requested_with', 'strtolower') == 'xmlhttprequest';
    }

    public function isCli()
    {
        if (!defined('PHP_SAPI')) {
            return false;
        }

        return PHP_SAPI == 'cli';
    }

    /**
     * @param $option
     */
    public function isMethod($option)
    {
        if (in_array($option, array('put', 'patch', 'delete', 'options'))) {
            return isset($_POST['_METHOD']) && strtolower($_POST['_METHOD']) == $option;
        }

        return Configure::server('request_method', 'strtolower') == $option;
    }

    /**
     * @return mixed
     */
    protected function parseCommandLine()
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

    protected function resolveGlobalRequest()
    {
        self::$globalRequest = $this->isCli() ? $this->parseCommandLine() : $this->uri->createRequest();
    }
}
