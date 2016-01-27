<?php

namespace Viloveul\Core;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Core
 */

use ReflectionClass;

abstract class Object
{
    /**
     * classname.
     *
     * @return string called classname
     */
    final public static function classname()
    {
        return get_called_class();
    }

    /**
     * availableMethods.
     *
     * @return array method lists
     */
    final public static function availableMethods()
    {
        return get_class_methods(self::classname());
    }

    /**
     * hasMethod.
     *
     * @param   string method name
     *
     * @return bool
     */
    final public static function hasMethod($methodname)
    {
        return in_array($methodname, self::availableMethods(), true);
    }

    /**
     * createInstance.
     *
     * @param   [mixed]
     *
     * @return object
     */
    final public static function createInstance($param = null)
    {
        $reflectionClass = new ReflectionClass(self::classname());

        return is_null($param) ?
            $reflectionClass->newInstance() :
                $reflectionClass->newInstanceArgs(func_get_args());
    }
}
