<?php
use \KungFoo\Routing\Router;

// standard includes
require_once '../config/config.php';

// include files that will most probably be needed anyways
require_once __DIR__.'/../src/App/Controllers/IndexController.php';

// if you want to use composer libraries, include the autoloader...
// require_once('../vendor/autoload.php');

// create the container from dependencies.php
$container = require_once '../src/dependencies.php';

// setup the router and start it, optionally using apcu_cache to speed up the routing beyond imagination
define('ROUTES_VERSION', 1); // increment the version to force a cache refresh

$router = new Router(
	HTTP_SUBDIR, // provide the subdir as string
	$container, // add the dependency injection container
	[
		'apcu_cache' =>  false, //'routes_v'.ROUTES_VERSION, // name of the cache or false
		'apcu_cache_ttl' => 900, // the ttl of the cache in seconds
	]
);

// check if we loaded routes from cache or not
if (!$router->routesLoaded()) {
	$router->addFromController('\App\Controllers\IndexController'); // call /test to run a signed test request!
}

echo $router->run(); // we will output anything that will be returned by the controllers
