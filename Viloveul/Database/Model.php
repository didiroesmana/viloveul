<?php

namespace Viloveul\Database;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use ArrayAccess;
use Viloveul\Core\Factory;

abstract class Model extends Factory implements ArrayAccess
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
        return static::resolve(get_called_class());
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
        return $this->offsetExists($name) ? $this->dataFields[$name] : null;
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
