<?php

namespace App\Controllers;

use KungFoo\Controllers\ExposableController;
use KungFoo\Routing\Request;
use App\Helpers\UniqueObjectsStore;

class IndexController extends ExposableController
{

	/**
	 * Expose this via get and post
	 *
	 * @exposeVia get
	 * @exposeVia post
	 *
	 * @exposeAs /test/$id
	 * @exposeAs /test
	 * 
	 * @inject database $db
	 * @inject myobject2 $anotherone
	 */
	public function testMethod(Request $request, $db = 'default', $anotherone = null, $id = 1234) {
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
	public function index(Request $request) {
		return $this->render('index', array('message' => 'this is Kungfoo'));
	}
}
