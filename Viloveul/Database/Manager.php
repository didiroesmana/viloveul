<?php

namespace Viloveul\Database;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Database
 */

use PDO;
use Exception;
use PDOException;

class Manager extends PDO implements IConnection
{
    protected $tables = array();

    protected $prefix;

    /**
     * Constructor.
     */
    public function __construct($dsn, $user, $pass, $prefix = '')
    {
        try {
            $this->prefix = $prefix;
            parent::__construct($dsn, $user, $pass);
            parent::setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new Exception(sprintf('Connection Failed : %s', $e->getMessage()));
        }
    }

    /**
     * Setter.
     *
     * @param   string name
     * @param   string table name
     */
    public function __set($name, $table)
    {
        $this->tables[$name] = $table;
    }

    /**
     * Getter.
     *
     * @Param   String name
     *
     * @return string prefixed table name
     */
    public function __get($name)
    {
        return isset($this->tables[$name]) ?
            $this->tables[$name] :
                $this->prepTable($name);
    }

    /**
     * prepTable.
     *
     * @param   string table name
     *
     * @return string table name with table prefix
     */
    public function prepTable($name, $protectIdentifier = true)
    {
        return (true === $protectIdentifier) ?
            $this->protectIdentifier($this->prefix.$name) :
                $this->prefix.$name;
    }

    /**
     * protectIdentifier.
     *
     * @param   string name
     *
     * @return string protected name with identifier
     */
    public function protectIdentifier($name)
    {
        if (strpos($name, '(') !== false) {
            preg_match('#(.+)\((.+)\)#', $name, $match);

            return $match[1].'('.$this->protectIdentifier($match[2]).')';
        }

        return implode('.', array_map(array($this, 'createIdentifier'), explode('.', $name)));
    }

    /**
     * createIdentifier.
     *
     * @param   string name
     *
     * @return string name
     */
    public function createIdentifier($name)
    {
        if ($name == '*') {
            return $name;
        }

        $driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);

        $backtick = array('mysql', 'sqlite', 'sqlite2');
        $quote = array('pgsql', 'sqlsrv', 'dblib', 'mssql', 'sybase', 'firebird');

        if (in_array($driver, $backtick, true)) {
            return "`{$name}`";
        } elseif (in_array($driver, $quote, true)) {
            return "'{$name}'";
        } else {
            return $name;
        }
    }

    /**
     * command.
     *
     * @param   string sql statement
     * @param   array bind-params
     *
     * @return object query
     */
    public function command($statement, $params = array())
    {
        try {
            $query = $this->prepare($statement);
            $query->execute($params);

            return $query;
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
