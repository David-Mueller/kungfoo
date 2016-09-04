<?php

/**
 * Testing the proper functioning of the ServiceLocator class
 *
 * User: david
 * Date: 4.9.16
 * Time: 5:15
 */
class ServiceLocatorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * test, if stored items can be retrieved
	 */
	function testServiceLocatorStoresItem() {
		$key = 'item1';

		$factory = function() use($key) {
			$object = new stdClass();
			$object->name = 'Obj'.$key;
			return $object;
		};

		$sl = new \KungFoo\Helpers\ServiceLocator();
		$this->assertFalse($sl->resolve($key));

		$sl->register($key, $factory);
		$response = $sl->resolve($key);
		$this->assertEquals('Obj'.$key, $response->name);

		// check if the resolved object is really a new instance
		$response->name = 'changed.';
		$response = $sl->resolve($key);
		$this->assertEquals('Obj'.$key, $response->name);

	}

	/**
	 * test if shared objects are really shared
	 */
	function testServiceLocatorStoresSharedItem() {
		$key = 'item1';

		$factory = function() use($key) {
			$object = new stdClass();
			$object->name = 'Obj'.$key;
			return $object;
		};

		$sl = new \KungFoo\Helpers\ServiceLocator();
		$this->assertFalse($sl->resolve($key));

		$sl->share($key, $factory);
		$response = $sl->resolve($key);
		$response->name = 'changed';

		// test if the second response is the same object
		$response2 = $sl->resolve($key);
		$this->assertEquals('changed', $response2->name);
	}

	/**
	 * Test,
	 * if the service locator of a factory-function contains shared objects
	 * and if params added to :resolve() are forwarded as factory parameters
	 */
	function testServiceLocatorPassesParams() {
		$key = 'item1';
		$key2 = 'item2';

		$factory = function(\KungFoo\Helpers\ServiceLocator $container, $parameter) use($key) {
			$object = new stdClass();
			$object->name = 'Obj'.$key.'-'.$parameter;
			return $object;
		};
		$factory2 = function(\KungFoo\Helpers\ServiceLocator $container) use($key) {
			$object2 = new stdClass();
			$object = $container->resolve($key);
			$object2->name = $object->name . ' read';
			return $object2;
		};

		$sl = new \KungFoo\Helpers\ServiceLocator();
		$sl->share($key, $factory);
		$sl->register($key2, $factory2);

		// check if passed params will be passed to constructors and if container objects
		// can be accessed from the loader function
		$response = $sl->resolve($key, 'shared');
		$response2 = $sl->resolve($key2);

		$this->assertEquals('Obj'.$key.'-shared', $response->name);
		$this->assertEquals('Obj'.$key.'-shared read', $response2->name);
	}
}
