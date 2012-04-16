<?php
App::uses('Router', 'Routing');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Controller', 'Controller');

App::uses('ComponentCollection', 'Controller');
App::uses('Component', 'Controller');
App::uses('CrudComponent', 'Crud.Controller/Component');

App::uses('Validation', 'Utility');

Class TestCrudComponent extends CrudComponent {

    /**
     * Test visibility wrapper
     */
    public function testValidateId($id) {
        return $this->validateId($id);
    }
}

/**
 * Crud Test Case
 *
 */
class CrudTestCase extends CakeTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$Collection = new ComponentCollection();
        $settings = array(
            'actions' => array(
                'index',
                'add',
                'edit',
                'view',
                'delete'
            )
        );
		$this->Crud = new TestCrudComponent($Collection, $settings);

        $request = new CakeRequest();
        $response = new CakeResponse();
        $controller = new Controller($request, $response); //$this->getMock('Controller');
        $controller->name = 'CrudTest';
        $controller->methods = array();
        $this->Crud->initialize($controller);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Crud);

		parent::tearDown();
	}

    public function testDefaultSettings() {
        // TODO throw an exception if actions is not defined
    }

    public function testvalidateIdIntValid() {
        $id = 1;
        $return = $this->Crud->testValidateId($id);
        $this->assertTrue($return, "Expected $id to be accepted as valid, it was rejected");
    }
}
