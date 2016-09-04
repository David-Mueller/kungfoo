<?php

/**
 * We will test running the expected controller methods
 * by overwriting the $_SERVER superglobal and
 * by throwing an Exception with a well defined message from inside the controller methods.
 *
 * Date: 4.9.16
 * Time: 14:40
 */
class RouterTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test, that ControllerWithAnnotationsDispatch:exposeIndexAll will be run via GET.
	 *
	 * @expectedException        \Exception
	 * @expectedExceptionMessage #called exposeIndexAll#
	 */
	function testRunIndexGet() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/';

		$r = new \KungFoo\Routing\Router();
		$result = $r->addFromController(ControllerWithAnnotationsDispatch::class);
		$this->assertTrue($result);

		$r->run();
	}

	/**
	 * Test, that ControllerWithAnnotationsDispatch:exposeFunc2 will be run via GET
	 * with an empty $param
	 *
	 * @expectedException        \Exception
	 * @expectedExceptionMessage #called exposeFunc2:#
	 */
	function testRunMethodWithParams() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/exposeFunc2';

		$r = new \KungFoo\Routing\Router();
		$result = $r->addFromController(ControllerWithAnnotationsDispatch::class);
		$this->assertTrue($result);

		$r->run();
	}

	/**
	 * Test, that ControllerWithAnnotationsDispatch:exposeFunc2 will be run via POST
	 * passing $param set to "123456post"
	 *
	 * @expectedException        \Exception
	 * @expectedExceptionMessage #called exposeFunc2:123456post#
	 */
	function testRunMethodWithParamsPost() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['REQUEST_URI'] = '/exposeFunc2/123456post';

		$r = new \KungFoo\Routing\Router();
		$result = $r->addFromController(ControllerWithAnnotationsDispatch::class);
		$this->assertTrue($result);

		$r->run();
	}

	/**
	 * Test, that ControllerWithAnnotationsDispatch:exposeFunc2 will be run via GET
	 * passing $param set to "123456get"
	 *
	 * @expectedException        \Exception
	 * @expectedExceptionMessage #called exposeFunc2:123456get#
	 */
	function testRunMethodWithParamsGet() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/exposeFunc2/123456get';

		$r = new \KungFoo\Routing\Router();
		$result = $r->addFromController(ControllerWithAnnotationsDispatch::class);
		$this->assertTrue($result);

		$r->run();
	}

	/**
	 * Test, that ControllerWithAnnotationsDispatch:exposeFunc2 will be run via PUT
	 *
	 * @expectedException        \Exception
	 * @expectedExceptionMessage #called exposeFunc2:123456put#
	 */
	function testRunMethodWithParamsPut() {
		$_SERVER['REQUEST_METHOD'] = 'PUT';
		$_SERVER['REQUEST_URI'] = '/exposeFunc2/123456put';

		$r = new \KungFoo\Routing\Router();
		$result = $r->addFromController(ControllerWithAnnotationsDispatch::class);
		$this->assertTrue($result);

		$r->run();
	}

	/**
	 * Test, that ControllerWithAnnotationsDispatch:exposeFunc2 will be run via PATCH
	 *
	 * @expectedException        \Exception
	 * @expectedExceptionMessage #called exposeFunc2:123456patch#
	 */
	function testRunMethodWithParamsPatch() {
		$_SERVER['REQUEST_METHOD'] = 'PATCH';
		$_SERVER['REQUEST_URI'] = '/exposeFunc2/123456patch';

		$r = new \KungFoo\Routing\Router();
		$result = $r->addFromController(ControllerWithAnnotationsDispatch::class);
		$this->assertTrue($result);

		$r->run();
	}

	/**
	 * Test, that ControllerWithAnnotationsDispatch:exposeFunc2 will be run via GET
	 * passing the injection container, an autowired object with object->name = 'object1'
	 * and $param set to "param1-get"
	 *
	 * @expectedException        \Exception
	 * @expectedExceptionMessage #called exposeFunc3:container-object1-getparam1-get#
	 */
	function testRunMethodWithParamsDefaultPath() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/router/exposeFunc3/param1-get';

		$container = $this->getMockBuilder(\KungFoo\Helpers\ServiceLocator::class)
			->setMethods(['resolve'])
			->getMock();

		$object = new stdClass();
		$object->message = 'object1';

		$container->expects($this->once())
			->method('resolve')
			->with('myobject')
			->willReturn($object);

		$r = new \KungFoo\Routing\Router('/router', $container);
		$result = $r->addFromController(ControllerWithAnnotationsDispatch::class);
		$this->assertTrue($result);

		$r->run();
	}

	/**
	 * Test, that ControllerWithAnnotationsDispatch:exposeFunc2 will be run via DELETE
	 * passing '123456del' as $param
	 *
	 * @expectedException        \Exception
	 * @expectedExceptionMessage #called exposeFunc2:123456del#
	 */
	function testRunMethodWithParamsDelete() {
		$_SERVER['REQUEST_METHOD'] = 'DELETE';
		$_SERVER['REQUEST_URI'] = '/exposeFunc2/123456del';

		$r = new \KungFoo\Routing\Router();
		$result = $r->addFromController(ControllerWithAnnotationsDispatch::class);
		$this->assertTrue($result);

		$r->run();
	}

	/**
	 * Test, if addFromController works as expected
	 */
	function testAddFromController() {
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$r = $this->getMockBuilder(\KungFoo\Routing\Router::class)
			->setMethods(['all', 'get'])
			->getMock();

		$r->expects($this->once())
			->method('all')
			->with('/exposeIndexAll', 'ControllerWithAnnotations2:exposeIndexAll', array())
			->willReturn(true);

		$r->expects($this->once())
			->method('get')
			->with(
					'/exposeTestInjectionsGet/$param1/$param2',
					'ControllerWithAnnotations2:exposeTestInjectionsGet',
					array(
						'__kfrequest__',
				    '__kfcontainer__',
				    'myobject',
				    '',
				    '')
			)
			->willReturn(true);

		/** @var \KungFoo\Routing\Router $r */
		$result = $r->addFromController(ControllerWithAnnotations2::class);
		$this->assertTrue($result);
	}

	/**
	 * Test, if addFromController ignores unsupported "@exposeVia" annotations
	 */
	function testAddFromControllerNotAllowed() {
		$_SERVER['REQUEST_METHOD'] = 'GET';

		$r = $this->getMockBuilder(\KungFoo\Routing\Router::class)
			->setMethods(['all', 'notallowed'])
			->getMock();

		$r->expects($this->never())
			->method('all');
		$r->expects($this->never())
			->method('notallowed');

		/** @var \KungFoo\Routing\Router $r */
		$result = $r->addFromController(ControllerWithAnnotationsErrors::class);
		$this->assertTrue($result);
	}
}
