<?php

namespace App\Controllers;

use KungFoo\Controllers\ExposableController;
use KungFoo\Routing\Request;
use KungFoo\Helpers\ServiceLocator;
use App\Helpers\UniqueObjectsStore;

class IndexController extends ExposableController
{
	/**
	 * Expose this method via get and post
	 *
	 * @exposeVia get
	 * @exposeVia post
	 *
	 * @exposeAs /test/$id
	 * @exposeAs /test
	 * 
	 * @inject database $db
	 * @inject myobject $anotherone
	 */
	public function testMethod(ServiceLocator $container, Request $request, $db = 'default', $anotherone = null, $id = 1234) {
		$test = $container->resolve('myobject2', 'nice1');

		var_dump($test);
		if ($request->method == 'post') {
			if (!$request->checkSignature(array('id'), $_POST)) {
				return 'Signature missmatch';
			}
			return 'Signature matched!!';
		}

		return $this->render('index', array('message' => 'This is called with id '.intval($id).'<br><br>'));
	}

	/**
	 * @exposeVia all
	 * @exposeAs /
	 */
	public function index() {
		return $this->render('index', array('message' => 'This is KungFoo'));
	}
}
