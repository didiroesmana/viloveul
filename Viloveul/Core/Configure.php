<?php

namespace Viloveul\Core;

/*
 * @author Fajrul Akbar Zuhdi
 * @email fajrulaz@gmail.com
 */

class Configure
{
    /**
     * @var array
     */
    protected static $configs = array();

    /**
     * @param  $followed
     * @return mixed
     */
    public static function baseurl($followed = '/')
    {
        /**
         * @var mixed
         */
        static $baseurl = null;

        if (is_null($baseurl)) {
            if ($config_baseurl = static::read('baseurl', 'trim')) {
                $baseurl = rtrim($config_baseurl, '/') . '/';
            } else {
                $host = static::server('http_host');
                if ($host != 'localhost') {
                    $url = (static::supportHttps() ? 'https://' : 'http://') . $host;
                } else {
                    $url = 'http://localhost';
                }
                $script_name = static::server('script_name');
                $base_script_filename = basename(static::server('script_filename'));
                $url .= substr($script_name, 0, strpos($script_name, $base_script_filename));
                $baseurl = rtrim($url, '/') . '/';
            }
        }

        if (!empty($followed) && '/' != $followed) {
            return $baseurl . ltrim($followed, '/');
        }

        return $baseurl;
    }

    /**
     * @param $name
     * @param $filter
     */
    public static function read($name, $filter = null)
    {
        $value = array_key_exists($name, static::$configs) ? static::$configs[$name] : null;
        return is_callable($filter) ? call_user_func($filter, $value) : $value;
    }

    /**
     * @param $name
     * @param $filter
     */
    public static function server($name, $filter = null)
    {
        $name = in_array($name, array('argv', 'argc'), true) ? $name : strtoupper($name);
        $value = isset($_SERVER[$name]) ? $_SERVER[$name] : null;
        return is_callable($filter) ? call_user_func($filter, $value) : $value;
    }

    /**
     * @param $followed
     */
    public static function siteurl($followed = '/')
    {
        /**
         * @var mixed
         */
        static $siteurl = null;

        if (is_null($siteurl)) {
            $index_page = static::read('index_page', 'trim');
            $siteurl = rtrim(static::baseurl("/{$index_page}"), '/');
        }

        $dynamic_url = $siteurl;

        if (!empty($followed) && '/' != $followed) {
            $parts = explode('?', $followed);
            $trailing_slash = (substr($parts[0], strlen($parts[0]) - 1, 1) == '/');

            $dynamic_url .= rtrim($parts[0], '/');

            if (!$trailing_slash) {
                $urlsuffix = static::read('url_suffix', 'trim');
                if ($urlsuffix && !preg_match('#' . $urlsuffix . '$#', $parts[0])) {
                    $dynamic_url .= $urlsuffix;
                }
            } else {
                $dynamic_url .= '/';
            }

            if (isset($parts[1]) && !empty($parts[1])) {
                $dynamic_url .= '?' . $parts[1];
            }
        }

        return $dynamic_url;
    }

    /**
     * @return mixed
     */
    public static function supportHttps()
    {
        /**
         * @var mixed
         */
        static $https = null;

        if (is_null($https)) {
            if (static::server('https', true) == 'on' || 1 == static::server('https')) {
                $https = true;
            } elseif (443 == static::server('server_port')) {
                $https = true;
            } else {
                $https = false;
            }
        }

        return $https;
    }

    /**
     * @param $data
     * @param $value
     */
    public static function write($data, $value = null)
    {
        if (is_string($data)) {
            return static::write(array($data => $value));
        }

        foreach ((array) $data as $key => $val) {
            if (!array_key_exists($key, static::$configs)) {
                static::$configs[$key] = $val;
            }
        }
    }
}
