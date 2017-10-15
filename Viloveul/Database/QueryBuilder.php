<?php

namespace Viloveul\Database;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Exception;

class QueryBuilder
{
    /**
     * @var array
     */
    protected $conditions = array();

    /**
     * @var array
     */
    protected $fieldset = array();

    /**
     * @var array
     */
    protected $groups = array();

    /**
     * @var array
     */
    protected $havings = array();

    /**
     * @var int
     */
    protected $limited = 0;

    /**
     * @var int
     */
    protected $marker = 0;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var array
     */
    protected $orders = array();

    /**
     * @var array
     */
    protected $relations = array();

    /**
     * @var mixed
     */
    protected $scenario;

    /**
     * @var array
     */
    protected $selections = array();

    /**
     * @var array
     */
    protected $tables = array();

    /**
     * @var array
     */
    protected $values = array();

    /**
     * @param $method
     * @param $params
     */
    public function __call($method, $params)
    {
        return call_user_func_array(array($this->scenario, $method), $params);
    }

    /**
     * @param IConnection $scenario
     */
    public function __construct(IConnection $scenario)
    {
        $this->scenario = $scenario;
    }

    /**
     * @return mixed
     */
    public function clear()
    {
        $this->fieldset = array();
        $this->conditions = array();
        $this->selections = array();
        $this->tables = array();
        $this->joins = array();
        $this->orders = array();
        $this->limited = 0;
        $this->offset = 0;
        $this->groups = array();
        $this->havings = array();
        $this->values = array();
        $this->marker = 0;

        return $this;
    }

    /**
     * @param  $table
     * @param  $prepTable
     * @param  true         $protectIdentifier
     * @return mixed
     */
    public function from($table, $prepTable = true, $protectIdentifier = true)
    {
        $this->tables[] = array($table, $prepTable, $protectIdentifier);
        return $this;
    }

    public function get()
    {
        if (count($this->tables) < 1) {
            throw new Exception('table not selected');
        }

        if (count($this->selections) < 1) {
            $selectionField = '*';
        } else {
            $selections = array();
            foreach ($this->selections as $selection) {
                $field = array_shift($selection);
                $pos = strpos($field, '|');
                if ($pos !== false) {
                    $selections[] = $this->trackAliases(
                        substr($field, 0, $pos),
                        substr($field, $pos),
                        $selection[0]
                    );
                } else {
                    $selections[] = (true === $selection[0]) ? $this->scenario->protectIdentifier($field) : $field;
                }
            }
            $selectionField = implode(', ', $selections);
        }

        $sql = "SELECT {$selectionField} ";

        $fromTable = '';
        $tables = array();
        foreach ($this->tables as $table) {
            $tableName = array_shift($table);
            $prepTable = array_shift($table);
            $protect = isset($table[0]) ? (boolean) $table[0] : true;
            $pos = strpos($tableName, '|');

            if ($pos !== false) {
                if (true === $prepTable) {
                    $tables[] = $this->trackAliases($this->scenario->prepTable(substr($field, 0, $pos), $protect),
                        $this->scenario->protectIdentifier($tableName));
                } else {
                    $tables[] = (true === $prepTable) ? $this->scenario->prepTable($tableName, false) : $tableName;
                }

            } else {
                if (true === $prepTable) {
                    $tables[] = $this->scenario->prepTable($tableName, $protect);
                } else {
                    $tables[] = (true === $protect) ? $this->scenario->protectIdentifier($tableName) : $tableName;
                }

            }
        }
        $sql .= "FROM {$fromTable} ";
    }

    /**
     * @param  $table
     * @param  $on
     * @param  $mode
     * @return mixed
     */
    public function join($table, $on, $mode = 'inner')
    {
        $this->relations[] = array($table, $on, $mode);
        return $this;
    }

    /**
     * @param  $column
     * @param  $protectIdentifier
     * @return mixed
     */
    public function select($column, $protectIdentifier = true)
    {
        $fields = is_string($column) ? array_map('trim', explode(',', $column)) : (array) $column;

        $selections = &$this->selections;

        array_walk($fields, function ($field) use (&$selections, $protectIdentifier) {
            $selections[] = array($field, $protectIdentifier);
        });

        return $this;
    }

    /**
     * @param $real
     * @param $alias
     * @param $protectIdentifier
     */
    public function trackAliases($real, $alias, $protectIdentifier = true)
    {
        if (true !== $protectIdentifier) {
            return sprintf('%s AS %s', $real, $alias);
        }
        return sprintf(
            '%s AS %s',
            $this->scenario->protectIdentifier($real),
            $this->scenario->protectIdentifier($alias)
        );
    }
};
