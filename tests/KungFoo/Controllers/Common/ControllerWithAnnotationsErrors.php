<?php

/**
 * ControllerWithAnnotationsErrors.php
 * User: david
 * Date: 4.9.16
 * Time: 4:14
 *
 */
class ControllerWithAnnotationsErrors extends \KungFoo\Controllers\ExposableController
{

	/**
	 * @exposeVia notallowed
	 * @exposeAs /
	 */
	public function exposeIndexNotallowed() {
		throw new Exception('exposeIndexNotallowed has been called, which shouldn\'t happen.');
	}
}
