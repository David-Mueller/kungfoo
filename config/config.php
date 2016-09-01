<?php

error_reporting(0);
ini_set('display_errors', 0);

define('APP_DIR', realpath(__DIR__.'/..'));
define('HTTP_SUBDIR', ''); // are we running from within a subdir?

// put your server specific configuration here
if (is_file(__DIR__.'/config_server.php')) {
	include_once __DIR__.'/config_server.php';
}

// the service locator will play an important role for injecting objects
require_once __DIR__.'/../src/KungFoo/Helpers/ServiceLocator.php';

// load global functions
include_once(APP_DIR.'/src/globalFunctions.php');

// include files that will most probably be needed anyways
require_once __DIR__.'/../src/KungFoo/Routing/Request.php';
require_once __DIR__.'/../src/KungFoo/Routing/Router.php';
require_once __DIR__.'/../src/KungFoo/Controllers/BaseController.php';
require_once __DIR__.'/../src/KungFoo/Controllers/ExposableController.php';
