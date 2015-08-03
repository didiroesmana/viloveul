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

	private static $instance;

	protected $offsetCollections = array();

	/**
	 * Constructor
	 * initialize dependencies
	 */

	public function __construct() {
		is_null(self::$instance) or die('application has been loaded');

		self::$instance =& $this;

		$this['settings'] = $this->share(function($c){
			$defaultSettings = array(
				'session_name' => 'viloveul'
			);

			return Configure::get('settings', function($config) use ($defaultSettings){
				$settings = is_array($config) ? $config : array();

				return array_merge(
					$defaultSettings,
					$settings
				);
			});
		});

		$this['input'] = $this->share(function($c){
			return new Http\Input();
		});

		$this['response'] = $this->share(function($c){
			return new Http\Response();
		});

		$this['uri'] = $this->share(function($c){
			return new Http\Uri(Http\Request::createFromGlobals());
		});

		$this['session'] = $this->share(function($c){
			return new Http\Session($c['settings']['session_name']);
		});

		$this['dispatcher'] = $this->share(function($c){
			return new Router\Dispatcher($c['routes']);
		});

		$this['routes'] = $this->share(function($c){
			return new Router\RouteCollection();
		});
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
		if ( ! is_null($name)) {
			$this->offsetCollections[$name] = $value;
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
		if ( ! $this->offsetExists($name) ) {
			return null;
		}

		return $this->isInvokable($this->offsetCollections[$name]) ?
			call_user_func($this->offsetCollections[$name], $this) :
				$this->offsetCollections[$name];
	}

	/**
	 * offsetUnset
	 * implementaion of ArrayAccess
	 * 
	 * @access	public
	 * @param	String collection name
	 */

	public function offsetUnset($name) {
		if ( $this->offsetExists($name) ) {
			unset($this->offsetCollections[$name]);
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
		return isset($this->offsetCollections[$name]);
	}

	/**
	 * handle
	 * add handler for request
	 * 
	 * @access	public
	 * @param	[mixed] String|Array
	 * @param	[mixed] String|Closure|Array
	 * @param	[mixed] Closure
	 */

	public function handle($route, $callable) {
		$params = func_get_args();

		if ( count($params) > 3 ) {
			throw new Exception('Parameter is only accepted maximal 3 arguments');
		}

		$handler = array_pop($params);

		$callback = (is_object($handler) && method_exists($handler, 'bindTo')) ?
			$handler->bindTo($this, $this) :
				$handler;

		if ( count($params) > 1 ) {
			$methods = array_shift($params);

			foreach ( (array) $methods as $method ) :
				if ( Http\Request::isMethod($method) ) {
					foreach ( (array) $params[0] as $routing ) {
						$this->routes[$routing] = $callback;
					}
				}
			endforeach;

		} else {

			foreach ( (array) $route as $routing ) :
				if ( isset($this->routes[$routing]) ) {
					continue;
				}

				$this->routes[$routing] = $callback;
			endforeach;

		}

		return $this;
		
	}

	/**
	 * run
	 * execute or running the application
	 */

	public function run() {
		$this->dispatcher->dispatch(Http\Request::createFromGlobals(), Configure::urlsuffix());

		$handler = $this->dispatcher->fetchHandler();

		if ( empty($handler) ) {
			throw new Exception('handler does not found');
		}

		try {

			$reflection = new ReflectionFunction($handler);
			$output = $reflection->invoke($this->dispatcher->fetchVars());
			$this->response->send($output);

		} catch (ReflectionException $e) {
			die($e->getMessage());
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
	 * share
	 * 
	 * @access	public
	 * @param	Closure
	 * @return	Object closure
	 */

	public static function share($callback) {
		return function($c) use ($callback) {
			static $object = null;

			if ( is_null($object) ) {
				$object = $callback($c);
			}

			return $object;
		};
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
