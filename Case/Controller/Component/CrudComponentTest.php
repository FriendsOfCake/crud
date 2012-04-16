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
    public function testGetSubject($additional = array()) {
        return $this->getSubject($additional);
    }

    /**
     * Test visibility wrapper
     */
    public function testRedirect($subject, $url = null) {
        return $this->redirect($subject, $url);
    }

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

		$this->Crud = $this->getMock(
        	    'TestCrudComponent',
	            null,
        	    array($Collection, $settings)
	        );

	        $controller = $this->getMock('Controller', array('header', '_stop', 'redirect'), array(), "", false);
        	$controller->name = 'Examples';

	        $request = new CakeRequest();
        	$response = new CakeResponse();

	        $controller->__construct($request, $response);

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

    public function testDeleteActionValidId() {
        $this->Crud->settings['validateId'] = 'notUuid';

        $id = 1;
//        $this->Crud->executeAction('delete', array($id));
    }

    public function testRedirect() {
	$this->Crud->controller->expects($this->atLeastOnce())->method('redirect');

	$subject = $this->Crud->testGetSubject();
	$this->Crud->testRedirect($subject);
    }

    public function testvalidateIdIntValid() {
	$this->Crud->controller->expects($this->never())->method('redirect');

        $this->Crud->settings['validateId'] = 'notUuid';

        $id = 1;
        $return = $this->Crud->testValidateId($id, 'int');
        $this->assertTrue($return, "Expected id $id to be accepted, it was rejected");
    }

    public function testvalidateIdIntInvalid() {
	$this->Crud->controller->expects($this->atLeastOnce())->method('redirect');

        $this->Crud->settings['validateId'] = 'notUuid';

        $id = 'abc';
        $return = $this->Crud->testValidateId($id, 'int');
        $this->assertFalse($return, "Expected id $id to be rejected, it was accepted");
    }

    public function testvalidateIdUUIDValid() {
	$this->Crud->controller->expects($this->never())->method('redirect');

        $id = '12345678-1234-1234-1234-123456789012';
        $return = $this->Crud->testValidateId($id);
        $this->assertTrue($return, "Expected id $id to be accepted, it was rejected");
    }

    public function testvalidateIdUUIDInvalid() {
	$this->Crud->controller->expects($this->atLeastOnce())->method('redirect');

	$id = 123;
        $return = $this->Crud->testValidateId($id, 'int');
        $this->assertFalse($return, "Expected id $id to be rejected, it was accepted");
    }
}
