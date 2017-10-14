<?php

namespace Viloveul\Core;

/*
 * @author Fajrul Akbar Zuhdi
 * @email fajrulaz@gmail.com
 */

class Events
{
    /**
     * @var array
     */
    protected static $listeners = array();

    /**
     * @param $name
     * @param $listener
     * @param $priority
     */
    public static function addListener($name, $listener, $priority = 8)
    {
        $idx = static::buildListenerId($name, $listener, $priority);
        static::$listeners[$name][$priority][$idx] = $listener;
    }

    /**
     * @param  $name
     * @param  array   $value
     * @return mixed
     */
    public static function trigger($name, array $value = array())
    {
        if (!isset(static::$listeners[$name])) {
            return $value;
        }

        $params = $value;

        do {
            foreach ((array) current(static::$listeners[$name]) as $callback):
                if (is_callable($callback)) {
                    $filtered = call_user_func_array($callback, $params);
                    if ($filtered !== null) {
                        if (is_array($filtered) || is_object($filtered)) {
                            $params = is_array($filtered) ? $filtered : get_object_vars($filtered);
                        } else {
                            $params[0] = $filtered;
                        }
                    }
                }
            endforeach;
        } while (false !== next(static::$listeners[$name]));

        return $params;
    }

    /**
     * @param  $name
     * @param  $listener
     * @return mixed
     */
    protected static function buildListenerId($name, $listener)
    {
        if (is_string($listener)) {
            return $listener;
        }

        $callback = is_object($listener) ? [$listener, ''] : (array) $listener;

        if (is_object($callback[0])) {
            return spl_object_hash($callback[0]) . $callback[1];
        } elseif (is_string($callback[0])) {
            return $callback[0] . '::' . $callback[1];
        }
    }
}
