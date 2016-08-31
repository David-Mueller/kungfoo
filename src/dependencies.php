<?php


// register all dependencies here
$GLOBALS['CONTAINER']->share('uniqueObjectsStore', function($ioc) {
	return new \App\Helpers\UniqueObjectsStore();
});

// dependencies here... they will be injected into the controller classes by name
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
