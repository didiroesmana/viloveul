<?php namespace Viloveul\Router;

/**
 * @author 		Fajrul Akbar Zuhdi <fajrulaz@gmail.com>
 * @package		Viloveul
 * @subpackage	Router
 */

use Exception;
use ReflectionMethod;

class Dispatcher {

	protected $routes;

	protected $handler;

	protected $params = array();

	protected $ns = "App\\Controllers\\";

	protected $controllerDirectory;

	protected $autoload = true;

	/**
	 * Constructor
	 * 
	 * @access	public
	 * @param	ArrayAccess instanceof \Viloveul\Core\RouteCollection
	 */

	public function __construct(RouteCollection $routes, $controllerDirectory = '/') {
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
	 * fetchParams
	 * 
	 * @access	public
	 * @return	Array
	 */

	public function fetchParams() {
		return $this->params;
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

		if (! empty($url_suffix) && '/' != $request) {
			$len = strlen($request);
			$last = substr($request, $len-1, 1);

			if ('/' != $last) {
				$request = preg_replace('#' . $url_suffix . '$#i', '', $request);
			}
		}

		foreach ($this->routes as $pattern => $target) {
			if (preg_match('#^'.$pattern.'$#i', $request, $matches)) {
				if ( is_string($target) ) {
					$request = preg_replace('#^'.$pattern.'$#i', $target, $request);
					continue;
				} elseif (is_object($target) && method_exists($target, '__invoke')) {
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

		if (class_exists($class, ($this->autoload === true))) {
			$availableMethods = get_class_methods($class);
			if (in_array('__invoke', $availableMethods, true)) {
				return $this->promote(
					function($args = array()) use($class){
						return call_user_func_array(new $class, $args);
					},
					array($request)
				);
			} elseif (in_array($method, $availableMethods, true)) {
				return $this->promote(
					function($args = array()) use($class, $method){
						$reflection = new ReflectionMethod($class, $method);
						return $reflection->invokeArgs(new $class, $args);
					},
					$vars
				);
			}
		}

		// Try again with 404_not_found (if any)
		if ($this->routes->has('404_not_found')) {
			// create handler from 404_not_found route
			$e404 = $this->routes->fetch('404_not_found');

			if (is_string($e404)) {
				list($eClass, $eMethod, $eVars) = $this->createSection($e404);

				if (class_exists($eClass, ($this->autoload === true))) {
					$eAvailableMethods = get_class_methods($eClass);
					if (in_array('__invoke', $eAvailableMethods, true)) {
						return $this->promote(
							function($args = array()) use($eClass){
								return call_user_func_array(new $eClass, $args);
							},
							array($request)
						);
					} elseif (in_array($eMethod, $eAvailableMethods, true)) {
						return $this->promote(
							function($args = array()) use($eClass, $eMethod){
								$reflection = new ReflectionMethod($eClass, $eMethod);
								return $reflection->invokeArgs(new $eClass, $args);
							},
							$eVars
						);
					}
				}

			} elseif (is_object($e404) && method_exists($e404, '__invoke')) {
				return $this->promote(
					function($args = array()) use($e404){
						return call_user_func_array($e404, $args);
					},
					array($request)
				);
			}
		}

		// throw exception if 404_not_found has no route
		throw new NoHandlerException('No Handler for request "' . $request . '"');
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
		$ns = $this->ns;

		$name = null;

		if (! empty($sections)) {
			do {

				$name = str_replace(' ', '', ucwords(str_replace('-', ' ', strtolower($sections[0]))));
				$path .= "/{$name}";

				if (is_dir($path) && ! is_file("{$path}.php")) {
					$ns .= "{$name}\\";
					array_shift($sections);
				}

			} while (next($sections) !== false);
		}

		if (empty($sections)) {
			$sections = is_file("{$path}/{$name}.php") ?
				array($name, 'index') :
					array('main', 'index');

		} elseif (! isset($sections[1])) {
			$sections[1] = 'index';
		}

		$class = $ns . str_replace(' ', '', ucwords(str_replace('-', ' ', strtolower($sections[0]))));
		$method = 'action' . str_replace(' ', '', ucwords(str_replace('-', ' ', strtolower($sections[1]))));
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

	protected function promote($handler, array $params = array()) {
		$this->params = array_filter($params, 'trim');
		$this->handler = $handler;
	}

}
