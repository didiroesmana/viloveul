<?php

namespace Viloveul\Router;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Router
 */

use Iterator;

class RouteCollection implements Iterator
{
    protected $collections = array();

    /**
     * add.
     *
     * @param   string
     * @param   string|object
     */
    public function add($pattern, $handler)
    {
        $this->collections[$pattern] = $handler;
    }

    /**
     * has.
     *
     * @param   string
     *
     * @return bool
     */
    public function has($pattern)
    {
        return array_key_exists($pattern, $this->collections);
    }

    /**
     * fetch.
     *
     * @param   string
     *
     * @return string|object|null
     */
    public function fetch($pattern)
    {
        return $this->has($pattern) ?
            $this->collections[$pattern] :
                null;
    }

    /**
     * Implements of Iterator.
     */
    public function current()
    {
        return current($this->collections);
    }

    /**
     * Implements of Iterator.
     */
    public function key()
    {
        return key($this->collections);
    }

    /**
     * Implements of Iterator.
     */
    public function next()
    {
        next($this->collections);
    }

    /**
     * Implements of Iterator.
     */
    public function rewind()
    {
        reset($this->collections);
    }

    /**
     * Implements of Iterator.
     */
    public function valid()
    {
        return null !== $this->key();
    }
}
