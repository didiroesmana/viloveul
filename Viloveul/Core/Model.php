<?php

namespace Viloveul\Core;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Core
 */

use Exception;
use Viloveul\Database\IConnection;

abstract class Model extends Object
{
    private static $modelCollections = array();

    protected $db;

    /**
     * Constructor.
     */
    public function __construct($connection = null)
    {
        if (strpos(parent::classname(), 'Model') === false) {
            throw new Exception(sprintf('the <i>name</i> of class "%s" must following with "Model"', parent::classname()));
        }

        $this->db = ($connection instanceof IConnection) ? $connection : (Connector::getConnection());
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
        $classname = __CLASS__;

        if (false === $param) {
            return parent::createInstance();
        } elseif ($param instanceof $class) {
            self::$modelCollections[$class] = $param;
        }

        if (!isset(self::$modelCollections[$class])) {
            self::$modelCollections[$class] = parent::createInstance($param);
        }

        return self::$modelCollections[$class];
    }
}
