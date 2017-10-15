<?php

namespace Viloveul\Core;

/**
 * @author Fajrul Akbar Zuhdi
 * @email fajrulaz@gmail.com
 */

abstract class Object
{
    final public static function availableMethods()
    {
        return get_class_methods(self::classname());
    }

    final public static function classname()
    {
        return get_called_class();
    }

    /**
     * @param $methodname
     */
    final public static function hasMethod($methodname)
    {
        return in_array($methodname, self::availableMethods(), true);
    }

    /**
     * @param $name
     */
    public function isInvokable($name)
    {
        return is_object($name) && method_exists($name, '__invoke');
    }

    /**
     * @param $param
     */
    public static function forge($param = true)
    {
        if (false === $param) {
            return parent::createInstance();
        }

        $classname = __CLASS__;

        if ($param instanceof $classname) {
            self::$modelCollections[$classname] = $param;
        } elseif (!isset(self::$modelCollections[$classname])) {
            parent::createInstance($param);
        }

        return self::$modelCollections[$classname];
    }
}
