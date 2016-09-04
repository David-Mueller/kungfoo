<?php

/**
 * ExposableControllerTest.php
 * User: david
 * Date: 4.9.16
 * Time: 4:16
 */
class ExposableControllerTest extends PHPUnit_Framework_TestCase
{

	public function testClassAnnotations()
	{
		$c = new ControllerWithAnnotations();
		$methods = $c::exposeMethods();
		$methods = $this->getMethodFromExposed($methods, 'exposeIndexAll');

		$this->assertEquals(1, sizeof($methods));

		$method = $methods[0];
		// the method has been exposed.
		$this->assertNotEmpty($method);
		$this->assertEmpty($method['parameters']);
		$this->assertEquals(1, sizeof($method['requestMethods']));
		$this->assertEquals('all', $method['requestMethods'][0]);
		$this->assertEquals('/test/', $method['route']);
	}

	public function testClassDefaultAnnotations()
	{
		$c = new ControllerWithAnnotations2();
		$methods = $c::exposeMethods();
		$methods = $this->getMethodFromExposed($methods, 'exposeIndexAll');

		$this->assertEquals(1, sizeof($methods));

		$method = $methods[0];
		// the method has been exposed.
		$this->assertNotEmpty($method);
		$this->assertEmpty($method['parameters']);
		$this->assertEquals(1, sizeof($method['requestMethods']));
		$this->assertEquals('all', $method['requestMethods'][0]);
		$this->assertEquals('/exposeIndexAll', $method['route']);
	}

	public function testClassAnnotationsMultiple()
	{
		$c = new ControllerWithAnnotations();
		$methods = $c::exposeMethods();
		$methods = $this->getMethodFromExposed($methods, 'exposeMultipleRoutesGetPostPutDelete');
		$this->assertEquals(2, sizeof($methods));

		$routesCorrect = array('/test/test2alt' => true, '/test/test2' => true);
		foreach ($methods as $method) {
			$this->assertNotEmpty($method);
			$this->assertEmpty($method['parameters']);
			$this->assertEquals(4, sizeof($method['requestMethods']));
			$this->assertEquals('get', $method['requestMethods'][0]);
			$this->assertEquals('post', $method['requestMethods'][1]);
			$this->assertEquals('put', $method['requestMethods'][2]);
			$this->assertEquals('delete', $method['requestMethods'][3]);
			unset($routesCorrect[$method['route']]);
		}
		$this->assertEmpty($routesCorrect); // all routes must have been found.
	}

	public function testClassAnnotationsInjections()
	{
		$c = new ControllerWithAnnotations();
		$methods = $c::exposeMethods();
		$methods = $this->getMethodFromExposed($methods, 'exposeGetInjections');
		$this->assertEquals(1, sizeof($methods));

		$method = $methods[0];
		$this->assertNotEmpty($method['parameters']);
		$this->assertEquals('__kfrequest__', $method['parameters'][0]);
		$this->assertEquals('__kfcontainer__', $method['parameters'][1]);
		$this->assertEquals('testobject', $method['parameters'][2]);
		$this->assertTrue(isset($method['parameters'][3]));

    $this->assertEquals($method['route'], '/test/exposeGetInjections/$id');
	}

	public function testClassAnnotationsDefaultInjections()
	{
		$c = new ControllerWithAnnotations();
		$methods = $c::exposeMethods();
		$methods = $this->getMethodFromExposed($methods, 'exposeDefaultNoobject');
		$this->assertEquals(1, sizeof($methods));

		$method = $methods[0];
		$this->assertEquals(4, sizeof($method['parameters']));
		$this->assertEquals('__kfrequest__', $method['parameters'][0]);
		$this->assertEquals('__kfcontainer__', $method['parameters'][1]);
		$this->assertEquals('', $method['parameters'][2]);
		$this->assertEquals('', $method['parameters'][3]);

    $this->assertEquals('all', $method['requestMethods'][0]);
    $this->assertEquals($method['route'], '/test/exposeDefaultNoobject/$object/$id');
	}


	private function getMethodFromExposed(&$exposed, $method) {
		$matches = array();
		foreach ($exposed as $expose) {
			if ($expose['method'] == $method) {
				$matches[] = $expose;
			}
		}
		return $matches;
	}

}
