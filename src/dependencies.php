<?php

// init ServiceLocator container
use \KungFoo\Helpers\ServiceLocator;
$container = new ServiceLocator();

// add dependencies here... they will be injected into the controller classes by name
// 
// registered dependencies 
// - can be accessed via the global $CONTAINER object ($CONTAINER->resolve('myobjectAlias'))
// - will be injected automatically into marked controller classes
// 
// to set up autowiring, mark your method with "@inject myobjectAlias $myParameterName"
// and use:
// $container->share('myobjectAlias', function($ioc) {
// 	return new \MyObject();
// });

$container->share('uniqueObjectsStore', function(ServiceLocator $ioc) {
	return new \App\Helpers\UniqueObjectsStore();
});

$container->register('myobject', function(ServiceLocator $ioc) {
	$inst = new stdClass();
	$inst->name = 'Wow';
	return $inst;
});

$container->register('myobject2', function(ServiceLocator $ioc, $param = 'awesome') {
	$inst = new stdClass();
	$inst->name = 'this is ' . $param;
	return $inst;
});

$container->register('database', function(ServiceLocator $ioc) {
	$inst = new stdClass();
	$inst->name = 'Database';
	return $inst;
});

// expose the container
return $container;
