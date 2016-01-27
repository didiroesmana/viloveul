<?php

namespace Viloveul\Core;

/**
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 */
class Events
{
    protected static $listeners = array();

    /**
     * buildListenerId.
     *
     * @param   string event name
     * @param   callable listener
     *
     * @return unique id
     */
    protected static function buildListenerId($name, $listener)
    {
        if (is_string($listener)) {
            return $listener;
        }

        $callback = is_object($listener) ?
            array($listener, '') :
                (array) $listener;

        if (is_object($callback[0])) {
            return spl_object_hash($callback[0]).$callback[1];
        } elseif (is_string($callback[0])) {
            return $callback[0].'::'.$callback[1];
        }
    }

    /**
     * addListener.
     *
     * @param   string event name
     * @param   callable listener
     * @param   int priority
     */
    public static function addListener($name, $listener, $priority = 8)
    {
        $idx = self::buildListenerId($name, $listener, $priority);
        self::$listeners[$name][$priority][$idx] = $listener;
    }

    /**
     * trigger.
     *
     * @param   string event name
     * @param   array values
     *
     * @return array manipulated values
     */
    public static function trigger($name, array $value = array())
    {
        if (!isset(self::$listeners[$name])) {
            return $value;
        }

        $params = $value;

        do {
            foreach ((array) current(self::$listeners[$name]) as $callback) :
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
        } while (false !== next(self::$listeners[$name]));

        return $params;
    }
}
