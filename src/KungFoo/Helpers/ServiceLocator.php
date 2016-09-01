<?php

namespace KungFoo\Helpers;

/**
 * a primitive service locator class supporting shared and unshared instances
 *
 * register services via 
 * 	$serviceLocator->register('servicename', function($c){ return new Service(); }); // the service container $c will be passed, contaning already registered services
 * 	$serviceLocator->share('servicename2', function($c){ return new Service(); });
 *
 * find and use services via
 * 	$serviceX = $serviceLocator->resolve('servicename1');
 * 	$serviceY = $serviceLocator->resolve('servicename1'); //  $serviceX and $serviceY each have their own instance
 * 	
 * 	$serviceA = $serviceLocator->resolve('servicename2');
 * 	$serviceB = $serviceLocator->resolve('servicename2'); //  $serviceA and $serviceB point to the same instance
 */
class ServiceLocator
{
	protected $container;
	protected $containerShared;
	private $containerSharedInstances;

	// register an object for creation upon calling resolve
	public function register($key, Callable $object) {
		$this->container[$key] = $object;
	}

	// register an object in singleton mode - the object will be reused
	public function share($key, Callable $object) {
		$this->containerShared[$key] = $object;
	}

	// resolve an object, creating or returning an instance while passing in the given parameters to the constructor
	public function resolve($key, ...$params) {

		if (!isset($this->container[$key]) && !isset($this->containerShared[$key])) {
			return false;
		}

		// the first param will be the service locator itself
		array_unshift($params, $this);

		// check if this is a shared object
		if (isset($this->containerShared[$key])) {
			if (isset($this->containerSharedInstances[$key])) {
				return $this->containerSharedInstances[$key];
			}
			$this->containerSharedInstances[$key] = call_user_func_array($this->containerShared[$key], $params);
			return $this->containerSharedInstances[$key];
		}

		// spawn and return an instance passing along the params
		return call_user_func_array($this->container[$key], $params);
	}
}
