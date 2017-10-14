<?php

namespace Viloveul;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use ArrayAccess;
use Closure;
use Exception;
use ReflectionException;
use ReflectionFunction;
use Viloveul\Core;
use Viloveul\Http;
use Viloveul\Router;

class Application implements ArrayAccess
{
    /**
     * @var array
     */
    protected $container = array();

    /**
     * mapping Collections.
     */
    protected $dataOffset = array();

    /**
     * @var mixed
     */
    private $basepath;

    /**
     * @var mixed
     */
    private $directory;

    /**
     * current Application instance.
     */
    private static $instance;

    /**
     * @param $path
     * @param array   $configs
     */
    public function __construct($path, $configs = array())
    {
        $directory = realpath($path) or die('application path is not exists.');

        $basepath = realpath(Core\Configure::server('script_filename'));

        $configs['directory'] = rtrim(str_replace('\\', '/', $directory), '/');

        $configs['basepath'] = rtrim(str_replace('\\', '/', $basepath), '/');

        $this->container = new class extends Core\Factory
        {

        };

        Core\Configure::write($configs);

        $this->container->alias(Http\Input::class, 'input');
        $this->container->alias(Http\Response::class, 'response');
        $this->container->alias(Http\Uri::class, 'uri');
        $this->container->alias(Http\Session::class, 'session');
        $this->container->alias(Router\Dispatcher::class, 'dispatcher');
        $this->container->alias(Router\RouteCollection::class, 'routeCollection');

        $this->container[Http\Session::class] = $this->share(function ($c) {
            $name = Core\Configure::read('session_name', function ($value) {
                return empty($value) ? 'zafex' : $value;
            });
            return new Http\Session($name);
        });
        $this->container[Router\Dispatcher::class] = $this->share(function ($c) use ($directory) {
            return new Router\Dispatcher($c->routeCollection, "{$directory}/Controllers");
        });
        $this->container[Router\RouteCollection::class] = $this->share(function ($c) {
            return new Router\RouteCollection();
        });

        spl_autoload_register(array($this, 'autoloadClass'));
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->container->{$name};
    }

    /**
     * @param $class
     */
    public function autoloadClass($class)
    {
        $class = ltrim($class, '\\');
        $name = str_replace('\\', '/', $class);

        if (0 === strpos($name, 'App/')) {
            $location = $this->directory . '/' . substr($name, 4);
            $this->locateClass($location);

        } elseif (false === strpos($name, '/')) {
            $location = $this->directory . '/Libraries';

            /*
             * /var/www/public_html/your_app/Libraries/LibName/SameName/.../SameName/SameName.php
             */

            do {
                $location .= '/' . $name;
                if (false !== $this->locateClass($location)) {
                    break;
                }
            } while (is_dir($location));
        }
    }

    public static function basepath()
    {
        return self::$instance->basepath;
    }

    /**
     * @param  $class
     * @param  $name
     * @return mixed
     */
    public function bind($class, $name = null)
    {
        if (is_null($name)) {
            $args = explode('/', str_replace('\\', '/', $class));
            $name = array_pop($args);
        }

        $as = lcfirst($name);

        $this->container[$name] = $this->share(function ($c) use ($class) {
            return new $class($c);
        });

        return $this;
    }

    public static function currentInstance()
    {
        return self::$instance;
    }

    public static function directory()
    {
        return self::$instance->directory;
    }

    /**
     * @param $object
     */
    public function isInvokable($object)
    {
        return is_object($object) && method_exists($object, '__invoke');
    }

    /**
     * @param $location
     */
    public static function locateClass($location)
    {
        if (!is_file("{$location}.php")) {
            return false;
        }
        require_once "{$location}.php";
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function offsetExists($name)
    {
        return $this->container->offsetExists($name);
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->container->offsetGet($name);
    }

    /**
     * @param  $name
     * @param  $value
     * @return mixed
     */
    public function offsetSet($name, $value)
    {
        return $this->container->offsetSet($name, $value);
    }

    /**
     * @param  $name
     * @return mixed
     */
    public function offsetUnset($name)
    {
        return $this->container->offsetUnset($name);
    }

    /**
     * @param  $arg1
     * @param  $arg2
     * @return mixed
     */
    public function route($arg1, $arg2)
    {
        $params = func_get_args();
        $handler = array_pop($params);
        $callback = (is_object($handler) && method_exists($handler, 'bindTo')) ? $handler->bindTo($this, $this) : $handler;

        if (count($params) > 1) {
            $methods = array_shift($params);
            foreach ((array) $methods as $method) {
                if (Http\Request::isMethod($method) || 'any' === $method) {
                    foreach ((array) $params[0] as $key) {
                        $this->routeCollection->has($key) or $this->routeCollection->add($key, $callback);
                    }
                }
            }
        } else {
            foreach ((array) $params[0] as $key) {
                $this->routeCollection->has($key)
                or $this->routeCollection->add($key, $callback);
            }

        }

        return $this;
    }

    public function run()
    {
        $this->dispatcher->dispatch(Http\Request::createFromGlobals(), Core\Configure::read('url_suffix'));

        $handler = $this->dispatcher->fetchHandler();

        if (empty($handler)) {
            throw new Exception('handler does not found');
        }

        try {
            $reflection = new ReflectionFunction($handler);
            $output = $reflection->invoke($this->dispatcher->fetchParams());
            $this->response->send($output);
        } catch (ReflectionException $e) {
            Core\Debugger::handleException($e);
        }
    }

    /**
     * @param  Closure $callback
     * @return mixed
     */
    public function share(Closure $callback)
    {
        $handler = function () use ($callback) {

            /**
             * @var mixed
             */
            static $object = null;

            if (is_null($object)) {
                $object = $callback($this);
            }

            return $object;
        };

        return $handler->bindTo($this, $this);
    }
}
