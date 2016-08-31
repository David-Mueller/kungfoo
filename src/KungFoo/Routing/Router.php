<?php
/**
 * @author  David Müller <david@dmwe.de> - 2016
 */

namespace KungFoo\Routing;

use \KungFoo\Helpers\ServiceLocator;

/**
 * Simple Router that aims to stay performant while offering easy implementation
 *
 * $router = new Router();
 * $router->get('/api/user/$id/$fileid', 'Controller:function');
 * $router->all('/api/closure/$id', function($params) { die('We got id ' . $params['id']); });
 * $router->post('/api/files/$id/file/$fname', 'Controller::staticfunction');
 * $router->put('/api/filesagain/$id/file/$fname', 'Controller:publicfunction');
 * $router->get('/api/user', 'Controller::functionAlsoPossibleButNotCalled');
 *
 * Can be used via annotated Classes via router->addFromController('Controller') if Controller is built like:
 *     /**
 *      * @exposeVia /api/latest
 *      * @exposeVia /api/v2
 *      * /
 *     class Controller extends \KungFoo\Controllers\ExposableController {
 *      /**
 *       * exposeAs is optional - if unset, the route is derived from the method name and all defined parameters
 *       * @exposeVia get
 *       * @exposeVia post
 *       *
 *       * @exposeAs /saymyname/$name
 *       * /
 *       public function method($path, $name) {
 *         ...
 *       }
 *      }
 */
class Router
{
	protected $config;
	protected $routes;
	protected $routesComposed;
	protected $routePrefix;
	protected $counter;

	protected $parameterMapping;

	public function __construct($routePrefix = '', ServiceLocator $serviceLocator = null, array $config = array()) {

		$this->routes = $this->parameterMapping = array(
			'get'=>array(),
			'post'=>array(),
			'put'=>array(),
			'delete'=>array(),
			'patch'=>array(),
			'all'=>array(),
		);

		$this->counter = 0;
		$this->config = $config;
		$this->routePrefix = $routePrefix;

		if (!empty($config['apcu_cache'])) {
			// check if apcu is ready
			if (!function_exists('apcu_store')) {
				// no apcu -> disable cache.
				$this->config['apcu_cache'] = false;
			} else {
				$routesComposed = apcu_fetch($config['apcu_cache']);
				if (is_array($routesComposed) && !empty($routesComposed)) {
					$this->routesComposed = $routesComposed;
				}
			}
		}

		if (!empty($serviceLocator) && $serviceLocator instanceof ServiceLocator) {
			$this->container = $serviceLocator;
		}

	}

	public function get($route, $callable, $parameterMapping = array()) {
		$this->routes['get'][$this->counter++] = array('route' => $route, 'callable' => $callable, 'params' => $parameterMapping);
		$this->parameterMapping['get'][$route] = $parameterMapping;
		$this->recomposeRoutes();
	}
	public function post($route, $callable, $parameterMapping = array()) {
		$this->routes['post'][$this->counter++] = array('route' => $route, 'callable' => $callable, 'params' => $parameterMapping);
		$this->parameterMapping['post'][$route] = $parameterMapping;
		$this->recomposeRoutes();
	}
	public function put($route, $callable, $parameterMapping = array()) {
		$this->routes['put'][$this->counter++] = array('route' => $route, 'callable' => $callable, 'params' => $parameterMapping);
		$this->parameterMapping['put'][$route] = $parameterMapping;
		$this->recomposeRoutes();
	}
	public function delete($route, $callable, $parameterMapping = array()) {
		$this->routes['delete'][$this->counter++] = array('route' => $route, 'callable' => $callable, 'params' => $parameterMapping);
		$this->parameterMapping['delete'][$route] = $parameterMapping;
		$this->recomposeRoutes();
	}
	public function patch($route, $callable, $parameterMapping = array()) {
		$this->routes['patch'][$this->counter++] = array('route' => $route, 'callable' => $callable, 'params' => $parameterMapping);
		$this->parameterMapping['patch'][$route] = $parameterMapping;
		$this->recomposeRoutes();
	}
	public function all($route, $callable, $parameterMapping = array()) {
		$this->routes['all'][$this->counter++] = array('route' => $route, 'callable' => $callable, 'params' => $parameterMapping);
		$this->parameterMapping['all'][$route] = $parameterMapping;
		$this->recomposeRoutes();
	}

	/**
	 * Add routes for all functions that are exposed via exposeMethods()
	 */
	public function addFromController($classname, $mountPoint = '') {
		$allowedMethods = ['all','get','post','put','delete','patch'];

		// check if the arguments seem suitable
		if (!is_callable(array($classname, 'exposeMethods'))) { // must be instanceof ExposableController
			return false;
		}

		// get all methods that should be exposed
		$exposed = $classname::exposeMethods();
		foreach ($exposed as $expose) {
			// 'mount' each expose, but add the mountpoint as well
			$newroute = $mountPoint.$expose['route'];
			$newroute = rtrim($newroute, '/'); // remove trailing slashes
			if($newroute == '') {
				$newroute = '/';
			}
			// add a route for each requestMethod
			foreach ($expose['requestMethods'] as $requestMethod) {
				$requestMethod = strtolower($requestMethod);
				if (!in_array($requestMethod, $allowedMethods)) {
					continue;
				}
				$this->$requestMethod($newroute, $classname.':'.$expose['method'], $expose['parameters']);
			}
		}

		// recompose the routes as they have changed
		// prepares everything for dispatchRoute()
		$this->recomposeRoutes();
		return true;
	}

	/**
	 * startup the Router, dispatch to the first matching registered route handler
	 * @return mixed the route handler result
	 */
	public function run() {
		$requestUri = $_SERVER['REQUEST_URI'];

		// compose the routes, if not done already
		if (empty($this->routesComposed)) {
			$this->routesComposed = $this->composeRoutes();
		}

		return $this->dispatchRoute($requestUri);
	}

	/**
	 * Force the router to recompose its routes. This will update the cache as well
	 * @return mixed this
	 */
	public function recomposeRoutes() {
		$this->routesComposed = $this->composeRoutes();

		return $this;
	}

	/**
	 * return if we have routes composed already
	 * @return bool true or false
	 */
	public function routesLoaded() {
		return !empty($this->routesComposed);
	}

	/**
	 * 'formats' all of the added routes to be compatible with dispatch()
	 * @return array the new routesArray
	 */
	private function composeRoutes() {

		// filter the routes by request method
		$currentRequestMethod = $_SERVER['REQUEST_METHOD'];
		switch($currentRequestMethod) {
			default:
			case 'GET':
				$routes = $this->routes['all'] + $this->routes['get'];
				break;
			case 'POST':
				$routes = $this->routes['all'] + $this->routes['post'];
				break;
			case 'PUT':
				$routes = $this->routes['all'] + $this->routes['put'];
				break;
			case 'DELETE':
				$routes = $this->routes['all'] + $this->routes['delete'];
				break;
			case 'PATCH':
				$routes = $this->routes['all'] + $this->routes['patch'];
				break;
		}
		ksort($routes);

		// explode and format the currently active routes
		$routesArray = array();
		foreach ($routes as $route_el) {
			$route    = $route_el['route'];
			$callable = $route_el['callable'];
			$params   = $route_el['params'];

			$route = $this->routePrefix . $route;

			// we want to skip the first empty array element
			if ($route[0] == '/') {
				$route = substr($route, 1);
			}
			// make sure that prefix and route don't have double-slashes
			$route = str_replace(
				array('//'),
				array('/'),
				$route
			);
			// replace route into preg_match_all compatible syntax, including named matches
			$route = preg_replace ('/\$([\w-%]+)/i', '(?<${1}>[\w-%]+)', $route, -1);

			$route = str_replace(
				array('/'),
				array('\\/'),
				$route
			);

			$routesArray[] = array($callable, $route, $params);
		}

		if (!empty($this->config['apcu_cache'])) {
			$ttl = !empty($this->config['apcu_cache_ttl']) ? intval($this->config['apcu_cache_ttl']) : 900;
			apcu_store($this->config['apcu_cache'], $routesArray, $ttl);
		}

		return $routesArray;
	}

	/**
	 * find the first matching route handler and run it, providing all params and the used path to match it
	 * @param  string $path which path should be matched against?
	 * @return mixed       result of the route handler
	 */
	private function dispatchRoute($path) {
		// fetch precomposed routes or compose routes
		$routesArray = !empty($this->routesComposed) ? $this->routesComposed : $this->composeRoutes();

		// find the route of the current path
		$path = substr($path, 1);

		// now remove nonmatching routes one by one
		$matchingRoute = false;
		$variables_out = array();
		foreach ($routesArray as $testRoute) {
			$count = null;
			$output_array = array();
			$count = preg_match_all('/' . $testRoute[1] . '$/i', $path, $output_array);
			if ($count > 0) {
				// the first matching route will be fired!
				$matchingRoute = $testRoute;
				foreach ($output_array as $key => &$value) {
					if (!is_numeric($key)) {
						$variables_out[$key] = urldecode($value[0]);
					}
				}
				break;
			}
		}

		if (!empty($matchingRoute)) {
			return $this->dispatch(
				$matchingRoute[0],
				$path,
				$variables_out,
				$matchingRoute[2]
			);
		}
		return false;
	}

	/**
	 * run the route handler
	 * this might be a static function of a class ("class:function"),
	 * or a public function of a newly spawned instance ("class::function"),
	 * or a closure (causes problems with apcu caching),
	 * or a function name ("function")
	 * 
	 * all fetched params will be provided alongside the currently matched path (first param)
	 * 
	 * @param  mixed $callable the route handler - a callable expression
	 * @param  array $params   array of matched params
	 * @return mixed           result of the route handler
	 */
	private function dispatch($callable, $path, $params = null, $injectParameters = array()) {

		// we will always inject the Request object as $request
		$paramsIn = array();
		foreach ($injectParameters as $index => $type) {
			if ($type == 'kfrequest') {
		 		$paramsIn[$index] = new Request($path);
				continue;
			}
		 	if (!empty($type)) {
		 		$injectedInstance = $this->container->resolve($type);
		 		$paramsIn[$index] = $injectedInstance !== false ? $injectedInstance : null;
		 	} else {
		 		$paramsIn[$index] = array_pop($params);
		 	}
		 } 

		// call a closure
		if (is_object($callable) && ($callable instanceof \Closure)) {
			return call_user_func_array($callable, $paramsIn);
		}

		if (is_string($callable)) {
			if (($pos = strpos($callable, '::')) > 0) {
				// call static method
				return call_user_func_array($callable, $paramsIn);
			} elseif (($pos = strpos($callable, ':')) > 0) {
				// instantiate object and call public method
				$object = substr($callable, 0, $pos);
				$function = substr($callable, $pos+1);
				$instance = new $object;
				return call_user_func_array(array($instance,$function), $paramsIn);
			}
			// else, its probably a simple function
			return call_user_func_array($callable, $paramsIn);
		}
		return null;
	}
}
