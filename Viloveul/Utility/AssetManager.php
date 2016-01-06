<?php

namespace Viloveul\Utility;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Utility
 */

use Viloveul\Core\Configure;

class AssetManager
{
    protected static $loadedSources = array();

    protected static $registeredSources = array();

    /**
     * load.
     *
     * @param   string type
     * @param   string id
     * @param   string source target
     *
     * @return bool
     */
    public static function load($type, $id, $source)
    {
        $key = "{$id}-{$type}";

        // make sure the source is only printed one at once time

        if (!self::confirmLoaded($key, true)) {
            return;
        }

        $format = ($type == 'css') ?
            '<link rel="stylesheet" type="text/css" id="%s" href="%s" />' :
                '<script type="text/javascript" id="%s" src="%s"></script>';

        printf("{$format}\n", $key, sprintf($source, rtrim(Configure::baseurl(), '/')));

        return true;
    }

    /**
     * registerSource.
     *
     * @param   string id
     * @param   string source
     * @param   [mixed] String type
     *
     * @return bool
     */
    public static function registerSource($id, $source, $dependency = null, $type = null)
    {
        if (is_null($type)) {
            if (preg_match('#\.(css|js)$#', $source, $matches)) {
                $type = $matches[1];
            }
        }

        if (in_array($type, array('css', 'js'), true)) {
            $dependencies = empty($dependency) ? array() : (is_string($dependency) ? array($dependency) : (array) $dependency);
            self::$registeredSources["{$id}-{$type}"] = compact('source', 'dependencies');

            return true;
        }

        return false;
    }

    /**
     * printStyle.
     *
     * @param   string source id
     * @param   string|array dependency(s)
     *
     * @return bool
     */
    public static function printStyle($id)
    {
        return self::printScript($id, 'css');
    }

    /**
     * printScript.
     *
     * @param   string source id
     * @param   string|array dependency(s)
     * @param   string type
     *
     * @return bool
     */
    public static function printScript($id, $type = 'js')
    {
        $key = "{$id}-{$type}";

        if (!isset(self::$registeredSources[$key])) {
            return;
        }

        extract(self::$registeredSources[$key]);

        if (isset($dependencies) && !empty($dependencies)) {
            foreach ($dependencies as $dependency_id) {
                self::printScript($dependency_id, $type);
            }
        }

        self::load($type, $id, $source);

        return true;

        $format = ($type == 'css') ?
            '<link rel="stylesheet" type="text/css" id="%s" href="%s" />' :
                '<script type="text/javascript" id="%s" src="%s"></script>';

        printf("{$format}\n", $key, sprintf($source, rtrim(Configure::baseurl(), '/')));

        return true;
    }

    /**
     * confirmLoaded.
     *
     * @param   string key source
     * @param   bool autopush when not loaded
     *
     * @return bool
     */
    protected static function confirmLoaded($key, $autopush = false)
    {
        if (in_array($key, self::$loadedSources, false)) {
            return false;
        } elseif (true === $autopush) {
            array_push(self::$loadedSources, $key);
        }

        return true;
    }
}
