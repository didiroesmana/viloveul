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

	/**
	 * mapping before filter callbacks
	 */

	protected $beforeActions = array();

	/**
	 * mapping after filter callbacks
	 */

	protected $afterActions = array();

	/**
	 * mapping Collections
	 */

	protected $offsetCollections = array();

	/**
	 * Constructor
	 * initialize dependencies
	 */

	public function __construct() {
		is_null(self::$instance) or die('application has been loaded');

		self::$instance =& $this;

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
			$session_name = Configure::read('session_name', function($value){
				return empty($value) ? 'zafex' : $value;
			});
			return new Http\Session($session_name);
		});

		$this['dispatcher'] = $this->share(function($c){
			return new Router\Dispatcher($c['routes'], APPPATH . '/Controllers');
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
	 * filterAction
	 * 
	 * @access	public
	 * @param	Callable|Boolean
	 * @param	[mixed] Callable
	 * @return	void
	 */

	public function filterAction($filter) {
		$params = func_get_args();
		$handler = array_pop($params);
		if ( $this->isInvokable($handler) ) {
			if ( isset($params[0]) && $params[0] === true ) {
				$this->afterActions[] = $handler;
			} else {
				$this->beforeActions[] = $handler;
			}
		}
		return $this;
	}

	/**
	 * applyFilter
	 * 
	 * @access	public
	 * @param	Array
	 * @return	String
	 */

	public function applyFilter($filters) {
		$data = '';

		if ( count($filters) > 0 ) {
			$data = array_reduce($filters, function($carry, $item){
				ob_start();
					$filter = call_user_func($item);
					$output = ob_get_contents();
				ob_end_clean();
				if ( ! is_null($filter) ) {
					$carry .= $filter;
				} elseif ( ! is_null($output) ) {
					$carry .= $output;
				}
				return $carry;
			});
		}
		
		return $data;
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

		if ( empty($handler) ) {
			throw new Exception('handler does not found');
		}

		try {

			$reflection = new ReflectionFunction($handler);

			$before = $this->applyFilter($this->beforeActions);

			$output = $reflection->invoke($this->dispatcher->fetchVars());

			$after = $this->applyFilter($this->afterActions);

			$this->response->send($before.$output.$after);

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
