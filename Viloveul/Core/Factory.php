<?php

namespace Viloveul\Core;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Factory implements ArrayAccess
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

    /**
     * @param $alias
     */
    public function __get($alias)
    {
        if (array_key_exists($alias, static::$instanceAliases)) {
            return $this[static::$instanceAliases[$alias]];
        }
        return null;
    }

    /**
     * @param $for
     * @param $name
     */
    public function alias($name, $class)
    {
        static::$instanceAliases[$name] = $class;
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
            if (is_string($value)) {
                $this->alias($name, $value);
            } else {
                static::$containerInstances[$name] = $value;
            }
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
    protected static function resolve($class)
    {
        if (array_key_exists($class, static::$containerInstances)) {
            if (static::$containerInstances[$class] instanceof Closure) {
                return static::$containerInstances[$class]();
            } else {
                return static::$containerInstances[$class];
            }
        }

        try {
            $reflection = new ReflectionClass($class);
            if ($constructor = $reflection->getConstructor()) {
                if ($parameters = $constructor->getParameters()) {
                    $arguments = [];
                    foreach ($parameters as $parameter) {
                        $classname = $parameter->getName();
                        if (($type = $parameter->getType()) && !$type->isBuiltin()) {
                            array_push($arguments, static::resolve($type->__toString()));
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
