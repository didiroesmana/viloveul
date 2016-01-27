<?php

namespace Viloveul;

/**
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 */
class Configure
{
    protected static $configs = array();

    /**
     * baseurl.
     *
     * @param   string static content
     *
     * @return string url
     */
    public static function baseurl($followed = '/')
    {
        static $baseurl = null;

        if (is_null($baseurl)) {
            if ($config_baseurl = self::read('baseurl', 'trim')) {
                $baseurl = rtrim($config_baseurl, '/').'/';
            } else {
                $host = self::server('http_host');
                if ($host != 'localhost') {
                    $url = (self::supportHttps() ? 'https://' : 'http://').$host;
                } else {
                    $url = 'http://localhost';
                }
                $script_name = self::server('script_name');
                $base_script_filename = basename(self::server('script_filename'));
                $url .= substr($script_name, 0, strpos($script_name, $base_script_filename));
                $baseurl = rtrim($url, '/').'/';
            }
        }

        if (!empty($followed) && '/' != $followed) {
            return $baseurl.ltrim($followed, '/');
        }

        return $baseurl;
    }

    /**
     * siteurl.
     *
     * @param   string dynamic application url
     *
     * @return string application url
     */
    public static function siteurl($followed = '/')
    {
        static $siteurl = null;

        if (is_null($siteurl)) {
            $index_page = self::read('index_page', 'trim');
            $siteurl = rtrim(self::baseurl("/{$index_page}"), '/');
        }

        $dynamic_url = $siteurl;

        if (!empty($followed) && '/' != $followed) {
            $parts = explode('?', $followed);
            $trailing_slash = (substr($parts[0], strlen($parts[0]) - 1, 1) == '/');

            $dynamic_url .= rtrim($parts[0], '/');

            if (!$trailing_slash) {
                $urlsuffix = self::read('url_suffix', 'trim');
                if ($urlsuffix && !preg_match('#'.$urlsuffix.'$#', $parts[0])) {
                    $dynamic_url .= $urlsuffix;
                }
            } else {
                $dynamic_url .= '/';
            }

            if (isset($parts[1]) && !empty($parts[1])) {
                $dynamic_url .= '?'.$parts[1];
            }
        }

        return $dynamic_url;
    }

    /**
     * supportHttps.
     *
     * @return bool
     */
    public static function supportHttps()
    {
        static $https = null;

        if (is_null($https)) {
            if (self::server('https', true) == 'on' || 1 == self::server('https')) {
                $https = true;
            } elseif (443 == self::server('server_port')) {
                $https = true;
            } else {
                $https = false;
            }
        }

        return $https;
    }

    /**
     * server.
     *
     * @param   string name
     * @param   callable filter
     *
     * @return Any server information
     */
    public static function server($name, $filter = null)
    {
        $name = in_array($name, array('argv', 'argc'), true) ? $name : strtoupper($name);
        $value = isset($_SERVER[$name]) ? $_SERVER[$name] : null;

        return is_callable($filter) ?
            call_user_func($filter, $value) :
                $value;
    }

    /**
     * read.
     *
     * @param   string name
     * @param   callable filter
     *
     * @return Any
     */
    public static function read($name, $filter = null)
    {
        $value = array_key_exists($name, self::$configs) ?
            self::$configs[$name] :
                null;

        return is_callable($filter) ?
            call_user_func($filter, $value) :
                $value;
    }

    /**
     * write.
     *
     * @param   string name
     * @param   Any value
     */
    public static function write($data, $value = null)
    {
        if (is_string($data)) {
            return self::write(array($data => $value));
        }

        foreach ((array) $data as $key => $val) {
            if (!array_key_exists($key, self::$configs)) {
                self::$configs[$key] = $val;
            }
        }
    }
}
