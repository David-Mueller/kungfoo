<?php

/**
 * ControllerWithAnnotations.php
 * User: david
 * Date: 4.9.16
 * Time: 4:14
 *
 * @exposeAs /test
 */
class ControllerWithAnnotations extends \KungFoo\Controllers\ExposableController
{

	/**
	 * @exposeVia all
	 * @exposeAs /
	 */
	public function exposeIndexAll() {

	}

	/**
	 * @exposeVia get
	 * @exposeVia post
	 * @exposeVia put
	 * @exposeVia delete
	 * @exposeAs /test2alt
	 * @exposeAs /test2
	 */
	public function exposeMultipleRoutesGetPostPutDelete() {

	}


	/**
	 * @exposeVia get
	 *
	 * @inject testobject $object
	 */
	public function exposeGetInjections($request, $container, $object, $id) {

	}

	/**
	 * @exposeVia
	 *
	 * @inject testobject
	 */
	public function exposeDefaultNoobject($request, $container, $object, $id) {

	}
}
