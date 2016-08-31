<?php

// init ServiceLocator container
use \KungFoo\Helpers\ServiceLocator;
$GLOBALS['CONTAINER'] = new ServiceLocator();

// add dependencies here... they will be injected into the controller classes by name
// 
// registered dependencies 
// - can be accessed via the global $CONTAINER object ($CONTAINER->resolve('myobjectAlias'))
// - will be injected automatically into marked controller classes
// 
// to set up autowiring, mark your method with "@inject myobjectAlias $myParameterName"
// and use:
// $GLOBALS['CONTAINER']->share('myobjectAlias', function($ioc) {
// 	return new \MyObject();
// });

$GLOBALS['CONTAINER']->share('uniqueObjectsStore', function($ioc) {
	return new \App\Helpers\UniqueObjectsStore();
});

$GLOBALS['CONTAINER']->register('myobject', function($ioc) {
	$inst = new stdClass();
	$inst->name = 'Wow';
	return $inst;
});

$GLOBALS['CONTAINER']->register('myobject2', function($ioc) {
	$inst = new stdClass();
	$inst->name = 'this is awesome';
	return $inst;
});


$GLOBALS['CONTAINER']->register('database', function($ioc) {
	$inst = new stdClass();
	$inst->name = 'Database';
	return $inst;
});
