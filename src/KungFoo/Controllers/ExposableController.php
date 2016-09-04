<?php
/**
 * @author  David Müller <david@dmwe.de> - 2016
 */

namespace KungFoo\Controllers;

abstract class ExposableController extends BaseController
{
	/**
	 * Parse annotations for the given $param
	 * @param  object $reflectionObject   should have a getDocComment() function available
	 * @param  string $param the name of the param to find
	 * @return mixed        null, true or array params
	 */
	private static function parseAnnotations($reflectionObject, $param) {
		$annotations = array();
		$matches     = preg_match_all('#@('.$param.')\s*(?<params>[\w/\$ :]*?)\n#s', $reflectionObject->getDocComment(), $annotations);

		// check if we found something
		if ($matches === 0) {
			return null;
		}

		// we will return true, if we found something at all but no params were given
		if (empty($annotations['params']) || empty($annotations['params'][0])) {
			return true;
		}
		// we will return the given params otherwise
		return $annotations['params'];
	}

	/**
	 * generate and return an array of method name and parameters of all exposed Methods
	 * Methods will be exposed using annotations
	 */
	public static function exposeMethods() {
		// we will return an array of exposed methods together with their routes
		$exposed = array();

		// uses derived class name
		$class         = new \ReflectionClass(static::class);
		$classExposeAs = static::parseAnnotations($class, 'exposeAs');

		// if not, dont add a prefix.
		if (empty($classExposeAs)) {
			$classExposeAs = array('');
		} elseif (!is_array($classExposeAs)) {
			$classExposeAs = array($classExposeAs);
		}

		// get all defined public methods
		$allmethods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

		foreach ($allmethods as $method) {
			// make sure the method doesnt come from this abstract class
			if ($method->class == self::class) {
				continue;
			}

			// check for annotations that might define a request method
			$exposeForMethods = static::parseAnnotations($method, 'exposeVia');
			if (empty($exposeForMethods)) {
				// the method should not be mounted anywhere
				continue;
			}

			if ($exposeForMethods === true) {
				// we should expose the method but we don't have a request method.
				// default to 'all'
				$exposeForMethods = array('all');
			} elseif (!is_array($exposeForMethods)) {
				// we allow to mount for multiple request methods (get, post, put, ...)
				$exposeForMethods = array($exposeForMethods);
			}

			// get all parameters
			$params = $method->getParameters();

			// extract the parameter names and the types in the right order
			// the syntax is
			//   @inject alias $varname somethingelsecomeshere andhere
			$injectParameters = static::parseAnnotations($method, 'inject');
			$parameterAliases = array();
			$parameterAliasesMapping = array();
			if (!empty($injectParameters)) {
				foreach ($injectParameters as $parameter) {
					// check if we have given a name like we should have!
					$split = explode(' ', $parameter, 3); // @inject alias $varname somethingelsecomeshere andhere
					if (sizeof($split) === 1) {
						continue;
					}
					$parameterAliasesMapping[ltrim($split[1],'$')] = $split[0];
				}
			}
			
			// get all of the aliases
			array_walk($params, function(&$el) use (&$parameterAliases, $parameterAliasesMapping) {

				if ($el->name === 'request') {
					$parameterAliases[]	= '__kfrequest__';
				} elseif ($el->name === 'container') {
					$parameterAliases[]	= '__kfcontainer__';
				} else {
					$type = is_callable($el, 'getType') ? (string) $el->getType() : '';
					$parameterAliases[] = isset($parameterAliasesMapping[$el->name]) ? $parameterAliasesMapping[$el->name] : $type;
				}
				$el = $el->name;
			});

			// now add request and container to $parameterAliasesMapping
			$parameterAliasesMapping['request'] = true;
			$parameterAliasesMapping['container'] = true;

			// get the path that should be used to mount the method
			// either from the exposeAs annotation or derived from the methods name and its params
			$exposeForRoutes = static::parseAnnotations($method, 'exposeAs');

			if(empty($exposeForRoutes)) {
				// no annotation given.
				// add params to the routes name
				$methodRoute = '';
				foreach ($params as $paramname) {
					if (!array_key_exists($paramname, $parameterAliasesMapping)) {
						$methodRoute .= '/$' . $paramname;
					}
				}
				$exposeForRoutes = array('/'.$method->name . $methodRoute);
			} elseif (!is_array($exposeForRoutes)) {
				$exposeForRoutes = array($exposeForRoutes);
			}

			// we allow to mount the method multiple times
			foreach ($exposeForRoutes as $methodRoute) {
				// we allow multiple class route prefixes given by the exposeVia annotation
				foreach ($classExposeAs as $routePrefix) {
					$exposed[] = array(
						'method' => $method->name,
						'parameters' => $parameterAliases,
						'requestMethods' => $exposeForMethods, // multiple request methods are allowed
						'route' => $routePrefix . $methodRoute // concatenate class route prefix and method route including params
					);
				}
			}
		} // check next method

		return $exposed;
	}
}
