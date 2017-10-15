<?php

namespace Viloveul;

/**
 * @email fajrulaz@gmail.com
 * @author Fajrul Akbar Zuhdi
 */

use Closure;
use Exception;
use ReflectionException;
use ReflectionFunction;
use Viloveul\Core;
use Viloveul\Http;
use Viloveul\Router;

class Application
{
    /**
     * @var array
     */
    protected $container = [];

    /**
     * mapping Collections.
     */
    protected $dataOffset = [];

    /**
     * current Application instance.
     */
    private static $app;

    /**
     * @var mixed
     */
    private $basepath;

    /**
     * @var mixed
     */
    private $directory;

    /**
     * @param $path
     * @param array   $configs
     */
    public function __construct($path, array $configs = [])
    {
        $directory = realpath($path) or die('application path is not exists.');

        $basepath = realpath(Core\Configure::server('script_filename'));

        $configs['directory'] = rtrim(str_replace('\\', '/', $directory), '/');

        $configs['basepath'] = rtrim(str_replace('\\', '/', $basepath), '/');

        // factory di extends ke anonymous class biar ngga ke-register di container

        $this->container = new class extends Core\Factory
        {
            // do nothing
        };

        Core\Configure::write($configs);

        $this->container->alias('uri', Http\Uri::class);
        $this->container->alias('input', Http\Input::class);
        $this->container->alias('request', Http\Request::class);
        $this->container->alias('response', Http\Response::class);
        $this->container->alias('session', Http\Session::class);
        $this->container->alias('dispatcher', Router\Dispatcher::class);
        $this->container->alias('routeCollection', Router\RouteCollection::class);

        $this->container[Http\Session::class] = $this->share(function ($c) {
            $name = Core\Configure::read('session_name', function ($value) {
                return empty($value) ? 'zafex' : $value;
            });
            return new Http\Session($name);
        });

        $this->container[Router\Dispatcher::class] = $this->share(function ($c) use ($configs) {
            return new Router\Dispatcher($c->routeCollection, "{$configs['directory']}/Controllers");
        });

        $this->container[Router\RouteCollection::class] = $this->share(function ($c) {
            return new Router\RouteCollection();
        });

        spl_autoload_register([$this, 'autoload']);

        static::$app = &$this;
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
     * @param  $name
     * @param  $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->container[$name] = $value;
    }

    /**
     * @param $class
     */
    public function autoload($class)
    {
        $class = ltrim($class, '\\');
        $name = str_replace('\\', '/', $class);

        if (0 === strpos($name, 'App/')) {
            $location = Core\Configure::read('directory') . '/' . substr($name, 4);
            $this->locateClass($location);

        } elseif (false === strpos($name, '/')) {
            $location = Core\Configure::read('directory') . '/Packages';

            /**
             * /var/www/public_html/your_app/Packages/LibName/SameName/.../SameName/SameName.php
             */

            do {
                $location .= '/' . $name;
                if (false !== $this->locateClass($location)) {
                    break;
                }
            } while (is_dir($location));
        }
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
     * any request method
     * $app->route('/', function(){ });
     *
     * spesifik
     * $app->route('get', '/', function(){ });
     *
     * more than one but not any
     * $app->route(['get', 'post', 'delete'], '/', function(){ });
     *
     * routing can be array
     * $app->route('any', ['/', '/home'], function(){ });
     *
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
                if ($this->request->isMethod($method) || 'any' === $method) {
                    foreach ((array) $params[0] as $key) {
                        $this->routeCollection->has($key) or $this->routeCollection->add($key, $callback);
                    }
                }
            }
        } else {
            foreach ((array) $params[0] as $key) {
                $this->routeCollection->has($key) or $this->routeCollection->add($key, $callback);
            }

        }

        return $this;
    }

    public function run()
    {
        $this->dispatcher->dispatch(
            $this->request->createFromGlobals(),
            Core\Configure::read('url_suffix')
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
