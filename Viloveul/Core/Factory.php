<?php

namespace Viloveul\Core;

/*
 * @author Fajrul Akbar Zuhdi
 * @email fajrulaz@gmail.com
 */

use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

abstract class Factory implements ArrayAccess
{
    /**
     * @var array
     */
    protected static $containerInstances = [];

    /**
     * @var array
     */
    protected static $instanceAliases = [];

    /**
     * @param $name
     * @param $params
     */
    public function __call($name, $params)
    {
        return static::__callStatic($name, $params);
    }

    /**
     * @param  $name
     * @param  $params
     * @return mixed
     */
    public static function __callStatic($name, $params)
    {
        try {
            $class = get_called_class();
            $method = 'call' . ucfirst($name);
            $reflection = new ReflectionMethod($class, $method);
            if (count($params) > 0) {
                return $reflection->invokeArgs(static::resolve($class), $params);
            } else {
                return $reflection->invoke(static::resolve($class));
            }
        } catch (ReflectionException $e) {
            throw $e;
        }
    }

    public function __construct()
    {
        static::$containerInstances[get_called_class()] = $this;
    }

    /**
     * @param $alias
     */
    public function __get($alias)
    {
        if (array_key_exists($alias, static::$instanceAliases)) {
            return static::resolve(static::$instanceAliases[$alias]);
        }
        return null;
    }

    /**
     * @param $for
     * @param $name
     */
    public function alias($name, $alias = null)
    {
        if (empty($alias)) {
            $parts = explode('\\', $name);
            $alias = end($parts);
        }
        static::$instanceAliases[lcfirst($alias)] = $name;
    }

    /**
     * @param $name
     */
    public function offsetExists($name)
    {
        return array_key_exists($name, static::$containerInstances);
    }

    /**
     * @param $name
     */
    public function offsetGet($name)
    {
        return static::resolve($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function offsetSet($name, $value)
    {
        if (!is_null($name)) {
            static::$containerInstances[$name] = $value;
        }
    }

    /**
     * @param $name
     */
    public function offsetUnset($name)
    {
        if (array_key_exists($name, static::$containerInstances)) {
            unset(static::$containerInstances[$name]);
        }
    }

    /**
     * @param $name
     * @param array   $params
     */
    public static function resolve($name = null, array $params = [])
    {
        $class = $name ?: get_called_class();

        if (array_key_exists($class, static::$containerInstances)) {
            if (static::$containerInstances[$class] instanceof Closure) {
                return static::$containerInstances[$class]();
            } else {
                return static::$containerInstances[$class];
            }
        }

        foreach ($params as $object) {
            if (is_object($object) && !array_key_exists(get_class($object), static::$containerInstances)) {
                static::$containerInstances[get_class($object)] = $object;
            }
        }

        try {

            $reflection = new ReflectionClass($class);
            if ($constructor = $reflection->getConstructor()) {
                if ($parameters = $constructor->getParameters()) {
                    $arguments = [];
                    foreach ($parameters as $parameter) {
                        $name = $parameter->getName();
                        if (($type = $parameter->getType()) && !$type->isBuiltin()) {
                            array_push($arguments, static::resolve($name));
                        } elseif (array_key_exists($name, $params)) {
                            array_push($arguments, $params[$name]);
                        } elseif ($parameter->isDefaultValueAvailable()) {
                            array_push($arguments, $parameter->getDefaultValue());
                        } elseif ($parameter->isArray()) {
                            array_push($arguments, []);
                        } elseif ($parameter->isCallable()) {
                            array_push($arguments, function () {
                                return null;
                            });
                        } elseif ($parameter->allowsNull()) {
                            array_push($arguments, null);
                        } else {
                            array_push($arguments, '');
                        }
                    }
                    static::$containerInstances[$class] = $reflection->newInstanceArgs($arguments);
                    return static::$containerInstances[$class];
                }
            }
            static::$containerInstances[$class] = $reflection->newInstance();
            return static::$containerInstances[$class];

        } catch (ReflectionException $e) {
            throw $e;
        }

        foreach (static::$containerInstances as $object) {
            if ($object instanceof $class) {
                return $object;
            }
        }
        throw new Exception("Error Processing Request");
    }
}
