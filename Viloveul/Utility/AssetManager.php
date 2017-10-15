<?php

namespace Viloveul\Utility;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Viloveul\Core\Configure;

class AssetManager
{
    /**
     * @var array
     */
    protected static $loadedSources = array();

    /**
     * @var array
     */
    protected static $registeredSources = array();

    /**
     * @param $type
     * @param $id
     * @param $source
     */
    public static function load($type, $id, $source)
    {
        $key = "{$id}-{$type}";

        // make sure the source is only printed one at once time

        if (!static::confirmLoaded($key, true)) {
            return;
        }

        if ($type == 'css') {
            $format = '<link rel="stylesheet" type="text/css" id="%s" href="%s" />';
        } else {
            $format = '<script type="text/javascript" id="%s" src="%s"></script>';
        }

        printf("{$format}\n", $key, sprintf($source, rtrim(Configure::baseurl(), '/')));

        return true;
    }

    /**
     * @param $id
     * @param $type
     */
    public static function printScript($id, $type = 'js')
    {
        $key = "{$id}-{$type}";

        if (!isset(static::$registeredSources[$key])) {
            return;
        }

        extract(static::$registeredSources[$key]);

        if (isset($dependencies) && !empty($dependencies)) {
            foreach ($dependencies as $dependency_id) {
                static::printScript($dependency_id, $type);
            }
        }

        static::load($type, $id, $source);

        return true;

        if ($type == 'css') {
            $format = '<link rel="stylesheet" type="text/css" id="%s" href="%s" />';
        } else {
            $format = '<script type="text/javascript" id="%s" src="%s"></script>';
        }

        printf("{$format}\n", $key, sprintf($source, rtrim(Configure::baseurl(), '/')));

        return true;
    }

    /**
     * @param $id
     */
    public static function printStyle($id)
    {
        return static::printScript($id, 'css');
    }

    /**
     * @param $id
     * @param $source
     * @param $dependency
     * @param null          $type
     */
    public static function registerSource($id, $source, $dependency = null, $type = null)
    {
        if (is_null($type)) {
            if (preg_match('#\.(css|js)$#', $source, $matches)) {
                $type = $matches[1];
            }
        }

        if (in_array($type, array('css', 'js'), true)) {
            $dependencies = empty($dependency) ? [] : (is_string($dependency) ? [$dependency] : (array) $dependency);
            static::$registeredSources["{$id}-{$type}"] = compact('source', 'dependencies');

            return true;
        }

        return false;
    }

    /**
     * @param $key
     * @param $autopush
     */
    protected static function confirmLoaded($key, $autopush = false)
    {
        if (in_array($key, static::$loadedSources, false)) {
            return false;
        } elseif (true === $autopush) {
            array_push(static::$loadedSources, $key);
        }

        return true;
    }
}
