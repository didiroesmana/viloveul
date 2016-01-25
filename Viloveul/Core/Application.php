<?php

namespace Viloveul\Core;

/*
 * @author      Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package     Viloveul
 * @subpackage  Core
 */

use Exception;
use ArrayAccess;
use Viloveul\Http;
use Viloveul\Router;
use ReflectionFunction;
use ReflectionException;

class Application implements ArrayAccess
{
    /**
     * current Application instance.
     */
    private static $instance;

    protected $realpath = null;

    protected $basepath = null;

    /**
     * mapping Collections.
     */
    protected $dataOffset = array();

    protected $collections = array();

    /**
     * Constructor
     * initialize dependencies.
     */
    public function __construct($realpath, $basepath)
    {
        is_null(self::$instance) or die('application has been initialized');

        self::$instance = $this;

        $this->collections['input'] = $this->share(function ($c) {
            return new Http\Input();
        });

        $this->collections['response'] = $this->share(function ($c) {
            return new Http\Response();
        });

        $this->collections['uri'] = $this->share(function ($c) {
            return new Http\Uri();
        });

        $this->collections['session'] = $this->share(function ($c) {
            $session_name = Configure::read('session_name', function ($value) {
                return empty($value) ? 'zafex' : $value;
            });

            return new Http\Session($session_name);
        });

        $this->collections['dispatcher'] = $this->share(function ($c) use ($realpath) {
            return new Router\Dispatcher($c->routeCollection, "{$realpath}/Controllers");
        });

        $this->collections['routeCollection'] = $this->share(function ($c) {
            return new Router\RouteCollection();
        });

        $this->realpath = $realpath;

        $this->basepath = $basepath;

        spl_autoload_register(array($this, 'autoloadClass'));
    }

    /**
     * Setter
     * its an aliases for offsetSet.
     *
     * @param   string collection name
     * @param   Any value of collection name
     */
    public function __set($name, $value)
    {
        $this->bind($value, $name);
    }

    /**
     * Getter
     * its an aliases for offsetGet.
     *
     * @param   string collection name
     *
     * @return Any
     */
    public function __get($name)
    {
        return $this->make($name);
    }

    /**
     * offsetSet
     * implementaion of ArrayAccess.
     *
     * @param   string collection name
     * @param   Any value of collection name
     */
    public function offsetSet($name, $value)
    {
        if (!is_null($name)) {
            $this->dataOffset[$name] = $value;
        }
    }

    /**
     * offsetGet
     * implementaion of ArrayAccess.
     *
     * @param   string collection name
     *
     * @return Any
     */
    public function offsetGet($name)
    {
        if (!$this->offsetExists($name)) {
            return;
        }

        return $this->isInvokable($this->dataOffset[$name]) ?
            call_user_func($this->dataOffset[$name], $this) :
                $this->dataOffset[$name];
    }

    /**
     * offsetUnset
     * implementaion of ArrayAccess.
     *
     * @param   string collection name
     */
    public function offsetUnset($name)
    {
        if ($this->offsetExists($name)) {
            unset($this->dataOffset[$name]);
        }
    }

    /**
     * offsetExists
     * implementaion of ArrayAccess.
     *
     * @param   string collection name
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        return isset($this->dataOffset[$name]);
    }

    /**
     * bind
     *
     * @param   String
     * @param   String
     *
     * @return  void
     */

    public function bind($class, $name = null)
    {
        if (is_null($name)) {
            $args = explode('/', str_replace('\\', '/', $class));
            $name = array_pop($args);
        }

        $as = lcfirst($name);

        $this->collections[$name] = $this->share(function($c) use($class){
            return new $class($c);
        });

        return $this;
    }

    /**
     * make
     *
     * @param   String
     *
     * @return  Object|NULL
     */

    public function make($name)
    {
        if (!array_key_exists($name, $this->collections)) {
            return null;
        }

        return $this->isInvokable($this->collections[$name]) ?
            call_user_func($this->collections[$name], $this) :
                $this->collections[$name];
    }

    /**
     * handle
     * add handler for request.
     *
     * @param   [mixed]
     * @param   [mixed]
     */
    public function handle($arg1, $arg2)
    {
        $params = func_get_args();
        $handler = array_pop($params);
        $callback = (is_object($handler) && method_exists($handler, 'bindTo')) ?
            $handler->bindTo($this, $this) :
                $handler;

        if (count($params) > 1) {
            $methods = array_shift($params);
            foreach ((array) $methods as $method) {
                if (Http\Request::isMethod($method) || 'any' === $method) {
                    foreach ((array) $params[0] as $key) {
                        $this->routeCollection->has($key)
                            or $this->routeCollection->add($key, $callback);
                    }
                }
            }
        } else {
            foreach ((array) $params[0] as $key) :
                $this->routeCollection->has($key)
                    or $this->routeCollection->add($key, $callback);
            endforeach;
        }

        return $this;
    }

    /**
     * run
     * execute or running the application.
     */
    public function run()
    {
        $this->dispatcher->dispatch(
            Http\Request::createFromGlobals(),
            Configure::read('url_suffix')
        );

        $handler = $this->dispatcher->fetchHandler();

        if (empty($handler)) {
            throw new Exception('handler does not found');
        }

        try {
            $reflection = new ReflectionFunction($handler);
            $output = $reflection->invoke($this->dispatcher->fetchParams());
            $this->response->send($output);
        } catch (ReflectionException $e) {
            Debugger::handleException($e);
        }
    }

    /**
     * isInvokable
     * check wether value is invokable or not.
     *
     * @param   object any
     *
     * @return bool
     */
    public function isInvokable($object)
    {
        return is_object($object) && method_exists($object, '__invoke');
    }

    /**
     * autoloadClass.
     *
     * @param   string
     */
    public function autoloadClass($class)
    {
        $class = ltrim($class, '\\');
        $name = str_replace('\\', '/', $class);
        $has = false;

        if (0 === strpos($name, 'App/')) {
            $location = $this->realpath().'/'.substr($name, 4);
            $this->locateClass($location);
        } elseif (false === strpos($name, '/')) {
            $location = $this->realpath().'/Libraries';

            /*
             * search file deeper
             * /var/www/public_html/your_app/Libraries/Name/Name/.../Name/Name.php
             */

            do {
                $location .= '/'.$name;
                if (false !== $this->locateClass($location)) {
                    break;
                }
            } while (is_dir($location));
        }
    }

    /**
     * locateClass.
     *
     * @param   string
     *
     * @return bool false when file does not exists
     */
    public static function locateClass($location)
    {
        if (!is_file("{$location}.php")) {
            return false;
        }
        require_once "{$location}.php";
    }

    /**
     * share.
     *
     * @param   Closure
     *
     * @return object closure
     */
    public static function share($callback)
    {
        return function ($c) use ($callback) {
            static $object = null;

            if (is_null($object)) {
                $object = $callback($c);
            }

            return $object;
        };
    }

    /**
     * realpath.
     *
     * @return string
     */
    public static function realpath()
    {
        return self::$instance->realpath;
    }

    /**
     * basepath.
     *
     * @return string
     */
    public static function basepath()
    {
        return self::$instance->basepath;
    }

    /**
     * &currentInstance.
     *
     * @return object application
     */
    public static function currentInstance()
    {
        return self::$instance;
    }
}
