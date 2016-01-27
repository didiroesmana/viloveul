<?php

namespace Viloveul\Core;

/**
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
    protected static $markedPoints = array();

    /**
     * mark.
     * 
     * @param	string name
     * @param	bool overwrite existing point
     */
    public static function mark($name, $overwrite = false)
    {
        if (!isset(self::$markedPoints[$name]) || true === $overwrite) {
            self::$markedPoints[$name] = microtime(true);
        }
    }

    /**
     * elapsedTime
     * calculate elapsed time marked.
     * 
     * @param	string marked name
     * @param	int count decimal
     *
     * @return string|int|float benchmarked
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
}
