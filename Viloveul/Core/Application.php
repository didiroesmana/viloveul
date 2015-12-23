<?php namespace Viloveul\Core;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Core
 */

use ArrayAccess;
use ReflectionFunction;
use ReflectionException;
use Exception;

use Viloveul\Http;
use Viloveul\Router;

class Application implements ArrayAccess {

	/**
	 * current Application instance
	 */

	private static $instance;

	protected $realpath = null;

	protected $basepath = null;

	/**
	 * mapping Collections
	 */

	protected $dataOffset = array();

	/**
	 * Constructor
	 * initialize dependencies
	 */

	public function __construct($realpath, $basepath) {
		is_null(self::$instance) or die('application has been initialized');

		self::$instance =& $this;

		$this['input'] = $this->share(function($c){
			return new Http\Input();
		});

		$this['response'] = $this->share(function($c){
			return new Http\Response();
		});

		$this['uri'] = $this->share(function($c){
			return new Http\Uri();
		});

		$this['session'] = $this->share(function($c){
			$session_name = Configure::read('session_name', function($value){
				return empty($value) ? 'zafex' : $value;
			});
			return new Http\Session($session_name);
		});

		$this['dispatcher'] = $this->share(function($c) use($realpath){
			return new Router\Dispatcher($c['routeCollection'], "{$realpath}/Controllers");
		});

		$this['routeCollection'] = $this->share(function($c){
			return new Router\RouteCollection();
		});

		$this->realpath = $realpath;

		$this->basepath = $basepath;

		spl_autoload_register(array($this, 'autoloadClass'));
	}

	/**
	 * Setter
	 * its an aliases for offsetSet
	 * 
	 * @access	public
	 * @param	String collection name
	 * @param	Any value of collection name
	 */

	public function __set($name, $value) {
		$this->offsetSet($name, $value);
	}

	/**
	 * Getter
	 * its an aliases for offsetGet
	 * 
	 * @access	public
	 * @param	String collection name
	 * @return	Any
	 */

	public function __get($name) {
		return $this->offsetGet($name);
	}

	/**
	 * offsetSet
	 * implementaion of ArrayAccess
	 * 
	 * @access	public
	 * @param	String collection name
	 * @param	Any value of collection name
	 */

	public function offsetSet($name, $value) {
		if (! is_null($name)) {
			$this->dataOffset[$name] = $value;
		}
	}

	/**
	 * offsetGet
	 * implementaion of ArrayAccess
	 * 
	 * @access	public
	 * @param	String collection name
	 * @return	Any
	 */

	public function offsetGet($name) {
		if (! $this->offsetExists($name)) {
			return null;
		}

		return $this->isInvokable($this->dataOffset[$name]) ?
			call_user_func($this->dataOffset[$name], $this) :
				$this->dataOffset[$name];
	}

	/**
	 * offsetUnset
	 * implementaion of ArrayAccess
	 * 
	 * @access	public
	 * @param	String collection name
	 */

	public function offsetUnset($name) {
		if ($this->offsetExists($name)) {
			unset($this->dataOffset[$name]);
		}
	}

	/**
	 * offsetExists
	 * implementaion of ArrayAccess
	 * 
	 * @access	public
	 * @param	String collection name
	 * @return	Boolean
	 */

	public function offsetExists($name) {
		return isset($this->dataOffset[$name]);
	}

	/**
	 * handle
	 * add handler for request
	 * 
	 * @access	public
	 * @param	[mixed]
	 * @return	void
	 */

	public function handle($param) {
		if (func_num_args() > 3) {
			// throw new Exception('Parameter is only accepted maximal 3 arguments');
		}

		$params = func_get_args();
		$handler = array_pop($params);

		$callback = (is_object($handler) && method_exists($handler, 'bindTo')) ?
			$handler->bindTo($this, $this) :
				$handler;

		if (count($params) > 1) {
			$methods = array_shift($params);
			foreach ((array) $methods as $method) {
				if (Http\Request::isMethod($method)) {
					foreach ((array) $params[0] as $key) {
						if (! $this->routeCollection->has($key)) {
							$this->routeCollection->add($key, $callback);
						}
					}
				}
			}
		} else {
			foreach ((array) $params[0] as $key) :
				if ($this->routeCollection->has($key)) {
					continue;
				}
				$this->routeCollection->add($key, $callback);
			endforeach;
		}
		return $this;
	}

	/**
	 * run
	 * execute or running the application
	 * 
	 * @access	public
	 * @return	void
	 */

	public function run() {
		$this->dispatcher->dispatch(Http\Request::createFromGlobals(), Configure::read('url_suffix'));

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
	 * check wether value is invokable or not
	 * 
	 * @access	public
	 * @param	Object any
	 * @return	Boolean
	 */

	public function isInvokable($object) {
		return is_object($object) && method_exists($object, '__invoke');
	}

	/**
	 * autoloadClass
	 * 
	 * @access	public
	 * @param	String
	 * @return	void
	 */

	public function autoloadClass($class) {
		$class = ltrim($class, '\\');
		$name = str_replace('\\', '/', $class);
		$has = false;

		if ( 0 === strpos($name, 'App/') ) {

			$location = $this->realpath().'/'.substr($name, 4);
			$this->locateClass($location);

		} elseif (false === strpos($name, '/')) {
			$location = $this->realpath().'/Libraries';

			/**
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
	 * locateClass
	 * 
	 * @access	public
	 * @param	String
	 * @return	Boolean false when file does not exists
	 */

	public static function locateClass($location) {
		if (! is_file("{$location}.php")) {
			return false;
		}
		require_once "{$location}.php";
	}

	/**
	 * share
	 * 
	 * @access	public
	 * @param	Closure
	 * @return	Object closure
	 */

	public static function share($callback) {
		return function($c) use ($callback) {
			static $object = null;

			if (is_null($object)) {
				$object = $callback($c);
			}

			return $object;
		};
	}

	/**
	 * realpath
	 * 
	 * @access	public
	 * @return	String
	 */

	public static function realpath() {
		return self::$instance->realpath;
	}

	/**
	 * basepath
	 * 
	 * @access	public
	 * @return	String
	 */

	public static function basepath() {
		return self::$instance->basepath;
	}

	/**
	 * &currentInstance
	 * 
	 * @access	public
	 * @return	Object application
	 */

	public static function &currentInstance() {
		return self::$instance;
	}

}
