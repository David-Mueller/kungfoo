<?php

namespace KungFoo;

class Bootstrap {
	static public function load() {

		// the service locator will play an important role for injecting objects
		require_once __DIR__.'/Helpers/ServiceLocator.php';

		// include files that will most probably be needed anyways
		require_once __DIR__.'/Routing/Request.php';
		require_once __DIR__.'/Routing/Router.php';
		require_once __DIR__.'/Controllers/BaseController.php';
		require_once __DIR__.'/Controllers/ExposableController.php';

	}	
}
