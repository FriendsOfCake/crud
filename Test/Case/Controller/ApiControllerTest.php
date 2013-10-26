<?php

App::uses('ErrorHandler', 'Error');
App::uses('Controller', 'Controller');
App::uses('CrudControllerTestCase', 'Crud.Test/Support');

/**
 * Our beautiful Crud Api test controller
 */
class CrudApiController extends Controller {

	public $components = array(
		'RequestHandler',
		'Crud.Crud' => array(
			'actions' => array('index'),
			'listeners' => array('Api')
		)
	);

	public function view($id) {
		throw new CakeException('yay');
	}

}

class ApiControllerTest extends CrudControllerTestCase {

	public function setUp() {
		parent::setUp();

		CakeLog::drop('stdout');
		CakeLog::drop('stderr');
	}

/**
 * Test that the API response rendering works
 *
 * @return void
 */
	public function testApiErrorResponse() {
		try {
			$this->testAction('/crud_api/view/1.json', array('method' => 'GET'));
		} catch (CakeException $exception) {
			ob_start();
			ErrorHandler::handleException($exception);
			$body = ob_get_clean();
		}

		$this->assertTrue(!empty($body), 'There must be a request body');
		$body = json_decode($body, true);
		$this->assertFalse(empty($body), 'The response must be valid json');
		$this->assertFalse($body['success'], 'Success key should be false');
		$this->assertTrue(!empty($body['data']['exception']), 'data.exception must be present in the response');
	}

}
