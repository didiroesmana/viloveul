<?php

namespace Viloveul\Core;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Core
 */

use ReflectionMethod;
use ReflectionException;

abstract class Controller extends Object
{
    private static $loadedControllers = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        self::$loadedControllers[self::classname()] = $this;
    }

    /**
     * To String.
     *
     * @return string classname
     */
    public function __toString()
    {
        return self::classname();
    }

    /**
     * Getter.
     *
     * @param   string name
     *
     * @return application collections
     */
    public function __get($name)
    {
        return Application::currentInstance()->make($name);
    }

    /**
     * fire
     * run controller as module.
     *
     * @param   string segment
     *
     * @return string output
     */
    public static function fire($requestSegment, $print = true)
    {
        $method = static::createActionName($requestSegment);

        if (!self::hasMethod($method)) {
            return false;
        }

        $classname = self::classname();

        try {
            $controller = isset(self::$loadedControllers[$classname]) ?
                self::$loadedControllers[$classname] :
                    self::createInstance();

            $ref = new ReflectionMethod($classname, $method);
            $output = $ref->invokeArgs($controller, $request);

            if (!is_null($output)) {
                if (true !== $print) {
                    return $output;
                }

                echo $output;

                return true;
            }
        } catch (ReflectionException $e) {
            Debugger::handleException($e);
        }
    }

    /**
     * createActionName
     *
     * @param   string
     * @return  string
     */
    protected static function createActionName($string)
    {
        $req = preg_split('/\//', $string, -1, PREG_SPLIT_NO_EMPTY);
        return 'action'.str_replace(' ', '', ucwords(str_replace('-', ' ', $req)));
    }
}
