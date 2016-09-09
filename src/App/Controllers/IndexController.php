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
	 * @exposeAs  /test/$id
	 * @exposeAs  /test
	 *
	 * @inject    database $db
	 * @inject    myobject $anotherone
	 *
	 * @param Request $request
	 * @param int     $id
	 * @return string
	 */
	public function testMethod(Request $request, $id = 1234, $err) {
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
