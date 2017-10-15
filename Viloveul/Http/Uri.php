<?php

namespace Viloveul\Http;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Viloveul\Core\Configure;

class Uri
{
    /**
     * @return mixed
     */
    public function createRequest()
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

    public function currentUrl()
    {
        $uriString = $this->createRequest();
        $query = Configure::server('query_string');

        return empty($query) ? Configure::siteurl($uriString) : Configure::siteurl("/{$uriString}?{$query}");
    }

    /**
     * @param $default
     */
    public function httpReferer($default = '')
    {
        return Configure::server('http_referer', function ($value) use ($default) {
            return is_null($value) ? $default : $value;
        });
    }
}
