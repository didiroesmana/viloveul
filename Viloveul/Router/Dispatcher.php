<?php namespace Viloveul\Router;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Router
 */

use Exception;
use ReflectionMethod;
use ReflectionException;

class Dispatcher {

	protected $routes;

	protected $handler;

	protected $vars = array();

	protected $nsClass = "App\\Controllers\\";

	protected $controllerDirectory;

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	ArrayAccess instanceof \Viloveul\Core\RouteCollection
	 */

	public function __construct(RouteCollection $routes, $controllerDirectory) {
		$this->routes = $routes;
		$this->controllerDirectory = realpath($controllerDirectory);
	}

	/**
	 * fetchHandler
	 * 
	 * @access	public
	 * @return	Closure handler
	 */

	public function fetchHandler() {
		return $this->handler;
	}

	/**
	 * fetchVars
	 * 
	 * @access	public
	 * @return	Array
	 */

	public function fetchVars() {
		return $this->vars;
	}

	/**
	 * dispatch
	 * 
	 * @access	public
	 * @param	String request
	 */

	public function dispatch($request_uri, $url_suffix = null) {
		// Make sure the request is started using slash
		$request = '/' . ltrim($request_uri, '/');

		if ( ! empty($url_suffix) && '/' != $request ) {
			$len = strlen($request);
			$last = substr($request, $len-1, 1);

			if ( '/' != $last ) {
				$request = preg_replace('#' . $url_suffix . '$#i', '', $request);
			}
		}

		foreach ($this->routes as $pattern => $target) {
			if ( preg_match('#^'.$pattern.'$#i', $request, $matches) ) {
				if ( is_string($target) ) {
					$request = preg_replace('#^'.$pattern.'$#i', $target, $request);
					continue;
				} elseif ( is_object($target) && method_exists($target, '__invoke') ) {
					$param_string = implode('/', array_slice($matches, 1));
					$this->promote(
						function($args = array()) use ($target){
							return call_user_func_array($target, $args);
						},
						$this->segmentToArray($param_string)
					);
					return true;
				}
			}
		}

		return $this->validate($request);
	}

	/**
	 * segmentToArray
	 * 
	 * @access	protected
	 * @param	String
	 * @return	Array
	 */

	protected function segmentToArray($string_segment) {
		return preg_split('/\//', $string_segment, -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Validate
	 * 
	 * @access	protected
	 * @param	String request
	 */

	protected function validate($request) {
		list($class, $method, $vars) = $this->createSection($request);
		// Set handler if exists

		if ( class_exists($class) ) {
			$availableMethods = get_class_methods($class);
			if ( in_array('__invoke', $availableMethods, true) ) {
				return $this->promote(
					function($args = array()) use($class){
						try {
							return call_user_func_array(new $class, $args);
						} catch (Exception $e) {
							die($e->getMessage());
						}
					},
					array($request)
				);
			} elseif ( in_array($method, $availableMethods, true) ) {
				return $this->promote(
					function($args = array()) use($class, $method){
						try {
							$reflection = new ReflectionMethod($class, $method);
							return $reflection->invokeArgs(new $class, $args);
						} catch (ReflectionException $e) {
							die($e->getMessage());
						}
					},
					$vars
				);
			}
		}

		// throw exception if 404_not_found has no route

		if ( ! isset($this->routes['404_not_found']) )
			throw new Exception('404 Not Found');

		// create handler from 404_not_found route

		if ( is_string($this->routes['404_not_found']) ) {
			list($eClass, $eMethod, $eVars) = $this->createSection($this->routes['404_not_found']);

			if ( class_exists($eClass) ) {
				$eAvailableMethods = get_class_methods($eClass);
				if ( in_array('__invoke', $eAvailableMethods, true) ) {
					return $this->promote(
						function($args = array()) use($eClass){
							try {
								return call_user_func_array(new $eClass, $args);
							} catch (Exception $e) {
								die($e->getMessage());
							}
						},
						array($request)
					);
				} elseif ( in_array($eMethod, $eAvailableMethods, true) ) {
					return $this->promote(
						function($args = array()) use($eClass, $eMethod){
							try {
								$reflection = new ReflectionMethod($eClass, $eMethod);
								return $reflection->invokeArgs(new $eClass, $args);
							} catch (ReflectionException $e) {
								die($e->getMessage());
							}
						},
						$eVars
					);
				}
			}
		}

		if ( is_object($this->routes['404_not_found']) && method_exists($this->routes['404_not_found'], '__invoke') ) {
			$e404 = $this->routes['404_not_found'];
			return $this->promote(
				function($args = array()) use($e404){
					return call_user_func_array($e404, $args);
				},
				array($request)
			);
		} else {
			throw new Exception('404 Not Found');
		}
	}

	/**
	 * createSection
	 * 
	 * @access	protected
	 * @param	String request
	 * @return	Array verified sections
	 */

	protected function createSection($request) {
		$sections = $this->segmentToArray($request);
		$path = $this->controllerDirectory;
		$ns = $this->nsClass;

		if ( ! empty($sections) ) {
			do {

				$name = implode('', array_map('ucfirst', explode('-', $sections[0])));
				$path .= "/{$name}";

				if ( is_dir($path) && ! is_file("{$path}.php") ) {
					$ns .= "{$name}\\";
					array_shift($sections);
				}

			} while ( next($sections) !== false );
		}

		if ( empty($sections) ) {
			$sections = array('main', 'index');
		} elseif ( ! isset($sections[1]) ) {
			$sections[1] = 'index';
		}

		$class = $ns . implode('', array_map('ucfirst', explode('-', strtolower($sections[0]))));
		$method = 'action' . implode('', array_map('ucfirst', explode('-', strtolower($sections[1]))));
		$vars = (count($sections) > 1) ? array_slice($sections, 2) : array();

		return array($class, $method, $vars);
	}

	/**
	 * promote
	 * 
	 * @access	protected
	 * @param	Closure handler
	 * @param	Array vars parameter
	 */

	protected function promote($handler, array $vars = array()) {
		$this->vars = array_filter($vars, 'trim');
		$this->handler = $handler;
	}

}
