<?php

namespace Viloveul\Core;

/*
 * @author Fajrul Akbar Zuhdi
 * @email fajrulaz@gmail.com
 */

/**
 * Example to use :.
 *
 * \Viloveul\Core\Benchmark::mark('something');
 *
 * do stuff
 *
 * echo \Viloveul\Core\Benchmark::elapsedTime('something');
 */

class Benchmark
{
    /**
     * @var array
     */
    protected static $markedPoints = array();

    /**
     * @param  $name
     * @param  $param
     * @return int
     */
    public static function elapsedTime($name, $param = 4)
    {
        if (isset(self::$markedPoints[$name])) {
            $args = array_slice(func_get_args(), 1);
            $decimal = array_pop($args);

            $start = self::$markedPoints[$name];

            if (($c = count($args)) > 0) {
                for ($i = 0; $i < $c; ++$i) {
                    self::mark($args[$i]);
                }
                $stop = self::$markedPoints[$args[0]];
            } else {
                $stop = microtime(true);
            }

            return number_format($stop - $start, $decimal);
        }

        return 0;
    }

    /**
     * @param $name
     * @param $overwrite
     */
    public static function mark($name, $overwrite = false)
    {
        if (!isset(self::$markedPoints[$name]) || true === $overwrite) {
            self::$markedPoints[$name] = microtime(true);
        }
    }
}
