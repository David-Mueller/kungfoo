<?php
use \KungFoo\Routing\Router;
use \KungFoo\Helpers\ServiceLocator;

require_once(__DIR__.'/../src/KungFoo/Helpers/ServiceLocator.php');

// init ServiceLocator container
$CONTAINER = new ServiceLocator();

// standard includes
require_once('../vendor/autoload.php');
require_once('../config/config.php');
require_once('../src/dependencies.php');

// include files that will most probably be needed anyways
require_once(__DIR__.'/../src/KungFoo/Routing/Router.php');
require_once(__DIR__.'/../src/KungFoo/Controllers/ExposableController.php');
require_once(__DIR__.'/../src/App/Controllers/IndexController.php');

// setup the router and start it
define('ROUTES_VERSION', 1); // increment the version to force a cache refresh

// inject a suitable service container?
$router = new Router(
	HTTP_SUBDIR, // provide the subdir as string
	[
		'apcu_cache' =>  'routes_v'.ROUTES_VERSION, // name of the cache
		'apcu_cache_ttl' => 900, // the ttl of the cache
		'dic' => $CONTAINER, // add the dependency injection container
	]
);

// check if we loaded routes from cache or not
if (!$router->routesLoaded()) {
	$router->addFromController('\App\Controllers\IndexController'); // call /test to run a signed test request!
}

echo $router->run(); // we will output anything that will be returned by the controllers
