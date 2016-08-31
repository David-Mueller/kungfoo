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

	public function register($key, Callable $object) {
		$this->container[$key] = $object;
	}

	public function share($key, Callable $object) {
		$this->containerShared[$key] = $object;
	}

	public function resolve($key) {
		if (!isset($this->container[$key]) && !isset($this->containerShared[$key])) {
			return false;
		}

		if (isset($this->containerShared[$key])) {
			if (isset($this->containerSharedInstances[$key])) {
				return $this->containerSharedInstances[$key];
			}
			$this->containerSharedInstances[$key] = $this->containerShared[$key]($this);
			return $this->containerSharedInstances[$key];
		}
		return $this->container[$key]($this); // we wont save the instances but create them on the fly
	}
}
