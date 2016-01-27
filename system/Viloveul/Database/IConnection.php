<?php

namespace Viloveul\Database;

/**
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 */
interface IConnection
{
    /**
     * Setter.
     */
    public function __set($name, $table);

    /**
     * Getter.
     */
    public function __get($name);

    /**
     * prepTable.
     *
     * @param   string
     * @param   string
     */
    public function prepTable($name, $protectIdentifier);

    /**
     * protectIdentifier.
     *
     * @param   string
     */
    public function protectIdentifier($name);

    /**
     * command.
     *
     * @param   string
     * @param   array
     */
    public function command($statement, $params);
}
