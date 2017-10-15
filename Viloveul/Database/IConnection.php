<?php

namespace Viloveul\Database;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

interface IConnection
{
    /**
     * Getter.
     */
    public function __get($name);

    /**
     * Setter.
     */
    public function __set($name, $table);

    /**
     * command.
     *
     * @param string
     * @param array
     */
    public function command($statement, $params);

    /**
     * prepTable.
     *
     * @param string
     * @param string
     */
    public function prepTable($name, $protectIdentifier);

    /**
     * protectIdentifier.
     *
     * @param string
     */
    public function protectIdentifier($name);
}
