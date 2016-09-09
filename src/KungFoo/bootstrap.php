<?php

// the service locator will play an important role for injecting objects
require_once __DIR__.'/Helpers/ServiceLocator.php';

// UniqueObjectStore will be used by the Request object
require_once __DIR__.'/Helpers/UniqueObjectStore.php';

// include files that will most probably be needed anyways
require_once __DIR__.'/Routing/Request.php';
require_once __DIR__.'/Routing/Router.php';
require_once __DIR__.'/Controllers/BaseController.php';
require_once __DIR__.'/Controllers/ExposableController.php';

// if you want to use composer libraries, include the autoloader...
require_once __DIR__.'/../vendor/autoload.php';

