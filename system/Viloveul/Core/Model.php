<?php

namespace Viloveul\Core;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Core
 */

use Exception;
use ArrayAccess;
use Viloveul\Database\IConnection;

abstract class Model extends Object implements ArrayAccess
{
    private static $modelCollections = array();

    protected $db;

    protected $classWrapper;

    private $dataFields = array();

    /**
     * Constructor.
     */
    public function __construct($class = __CLASS__)
    {
        $this->db = $this->dbConnection();
        $this->classWrapper = $class ? $class : __CLASS__;

        if (!isset(self::$modelCollections[__CLASS__])) {
            self::$modelCollections[__CLASS__] = $this;
        }
    }

    /**
     * dbConnection
     *
     * @return  object
     */
    public function dbConnection()
    {
        return Connector::getConnection();
    }

    /**
     * offsetExistss
     *
     * @param   string
     * @return  Boolean
     */
    public function offsetExists($name)
    {
        return array_key_exists($name, $this->dataFields) ? true : false;
    }

    /**
     * offsetUnset
     *
     * @param   string
     * @return  void
     */
    public function offsetUnset($name)
    {
        if ($this->offsetExists($name)) {
            unset($this->dataFields[$name]);
        }
    }

    /**
     * offsetGet
     *
     * @param   string
     * @return  mixed
     */
    public function offsetGet($name)
    {
        return $this->offsetExists($name) ?
            $this->dataFields[$name] :
                null;
    }

    /**
     * offsetSet
     *
     * @param   string
     * @param   mixed
     * @return  void
     */
    public function offsetSet($name, $value)
    {
        if (!is_null($name)) {
            $this->dataFields[$name] = $value;
        }
    }

    /**
     * forge.
     *
     * @param   [mixed] Boolean or Object
     *
     * @return object new|old Instance
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
