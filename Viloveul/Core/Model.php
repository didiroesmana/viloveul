<?php

namespace Viloveul\Core;

/*
 * @author Fajrul Akbar Zuhdi
 * @email fajrulaz@gmail.com
 */

use ArrayAccess;

abstract class Model extends Object implements ArrayAccess
{
    /**
     * @var mixed
     */
    protected $classWrapper;

    /**
     * @var mixed
     */
    protected $db;

    /**
     * @var array
     */
    private $dataFields = array();

    /**
     * @var array
     */
    private static $modelCollections = array();

    /**
     * @param $class
     */
    public function __construct($class = __CLASS__)
    {
        $this->db = $this->dbConnection();
        $this->classWrapper = $class ? $class : __CLASS__;

        if (!isset(self::$modelCollections[__CLASS__])) {
            self::$modelCollections[__CLASS__] = $this;
        }
    }

    public function dbConnection()
    {
        return Connector::getConnection();
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

    /**
     * @param $name
     */
    public function offsetExists($name)
    {
        return array_key_exists($name, $this->dataFields) ? true : false;
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->offsetExists($name) ?
        $this->dataFields[$name] :
        null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function offsetSet($name, $value)
    {
        if (!is_null($name)) {
            $this->dataFields[$name] = $value;
        }
    }

    /**
     * @param $name
     */
    public function offsetUnset($name)
    {
        if ($this->offsetExists($name)) {
            unset($this->dataFields[$name]);
        }
    }
}
