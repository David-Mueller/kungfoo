<?php

/**
 * ControllerWithAnnotationsDispatch.php
 * User: david
 * Date: 4.9.16
 * Time: 4:14
 *
 * @exposeVia /
 */
class ControllerWithAnnotationsDispatch extends \KungFoo\Controllers\ExposableController
{

	/**
	 * @exposeVia
	 *
	 * @inject myobject $object the given object
	 */
	public function exposeFunc3(
		\KungFoo\Helpers\ServiceLocator $container,
		\KungFoo\Routing\Request $request,
		$object,
		$param) {
		$containerMessage = !empty($container) ? 'container' : '';
		$objectMessage = !empty($object) ? $object->message : '';

		throw new \Exception(
			'#called exposeFunc3:'
			.$containerMessage . '-'
			.$objectMessage . '-'
			.$request->method
			.$param.'#');
	}

	/**
	 * @exposeVia get
	 * @exposeVia post
	 * @exposeVia put
	 * @exposeVia delete
	 * @exposeVia patch
	 *
	 * @exposeAs /func2/$param
	 * @exposeAs /func2
	 */
	public function exposeFunc2($param) {
		throw new \Exception('#called exposeFunc2:'.$param.'#');
	}

	/**
	 * @exposeVia all
	 * @exposeAs /
	 */
	public function exposeIndexAll() {
		throw new \Exception('#called exposeIndexAll#');
	}
}
