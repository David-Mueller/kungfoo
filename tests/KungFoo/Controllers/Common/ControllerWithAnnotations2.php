<?php

/**
 * ControllerWithAnnotations2.php
 * User: david
 * Date: 4.9.16
 * Time: 4:14
 *
 * @exposeVia /
 */
class ControllerWithAnnotations2 extends \KungFoo\Controllers\ExposableController
{

	/**
	 * @exposeVia all
	 */
	public function exposeIndexAll() {

	}

	/**
	 * @exposeVia get
	 *
	 * @inject myobject $object
	 */
	public function exposeTestInjectionsGet($request, $container, $object, $param1, $param2) {

	}
}
