<?php

namespace Viloveul\Database;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Exception;
use ReflectionClass;
use ReflectionException;
use Viloveul\Core\Configure;

class Connector
{
    /**
     * @var array
     */
    protected static $connections = array();

    /**
     * @var string
     */
    protected static $groupDefault = 'default';

    public static function defaultGroupGet()
    {
        return empty(static::$groupDefault) ? 'default' : (static::$groupDefault);
    }

    /**
     * @param $name
     */
    public static function defaultGroupSet($name)
    {
        static::$groupDefault = trim($name);
    }

    /**
     * @param $group
     * @param array    $params
     */
    public static function getConnection($group = null, $params = array())
    {
        if (empty($group)) {
            $group = static::defaultGroupGet();
        }
        if (!static::hasConnection($group)) {
            static::setConnection($group, $params);
        }

        return static::$connections[$group];
    }

    /**
     * @param $group
     */
    public static function hasConnection($group)
    {
        return array_key_exists($group, static::$connections);
    }

    /**
     * @param $group
     * @param array    $params
     */
    public static function setConnection($group, $params = array())
    {
        if (static::hasConnection($group)) {
            return false;
        }

        $class = '\\App\\Drivers\\DB\\' . implode('', array_map('ucfirst', explode('-', $group)));

        if (class_exists($class)) {
            try {
                $check = new ReflectionClass($class);
                if (!$check->implementsInterface('\\Viloveul\\Database\\IConnection')) {
                    throw new Exception('Database driver must implement of \\Viloveul\\Database\\IConnection');
                }
                static::$connections[$group] = $check->newInstance();
            } catch (ReflectionException $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            $config = Configure::read('db', function ($value) use ($group, $params) {
                return isset($value[$group]) ? $value[$group] : $params;
            });

            $dbconf = static::parseConfiguration($config);
            extract($dbconf);

            static::$connections[$group] = new Scenario($dsn, $username, $password, $prefix);
        }
    }

    /**
     * @param $params
     */
    protected static function parseConfiguration($params)
    {
        if (is_string($params)) {
            return array('dsn' => $params, 'username' => null, 'password' => null, 'prefix' => '');
        }

        $default = array(
            'driver' => 'mysql',
            'prefix' => '',
            'port' => 3306,
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbname' => '',
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
        );

        $dbconf = array_merge($default, (array) $params);

        $username = $dbconf['username'];
        $password = $dbconf['password'];
        $driver = $dbconf['driver'];
        $prefix = $dbconf['prefix'];

        unset($dbconf['username'], $dbconf['password'], $dbconf['driver'], $dbconf['prefix']);
        $dsn = sprintf('%s:%s', $driver, http_build_query($dbconf, '', ';'));

        return compact('dsn', 'username', 'password', 'prefix');
    }
}
