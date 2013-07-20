<?php

App::uses('Model', 'Model');
App::uses('CakeRequest', 'Network');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('IndexCrudAction', 'Crud.Controller/Crud/Action');
App::uses('CrudComponent', 'Crud.Controlller/Component');
App::uses('ComponentCollection', 'Controller');
App::uses('PaginatorComponent', 'Controller/Component');
App::uses('Controller', 'Controller');

class TestController extends Controller {

	public $paginate = array();
}

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class IndexCrudActionTest extends CakeTestCase {

// @codingStandardsIgnoreStart
	protected $ModelMock;

	protected $ActionMock;

	protected $RequestMock;

	protected $CrudMock;
// @codingStandardsIgnoreEnd

	public function setUp() {
		parent::setUp();

		$this->ModelMock = $this->getMockBuilder('Model');
		$this->ActionMock = $this->getMockBuilder('IndexCrudAction');
		$this->RequestMock = $this->getMockBuilder('CakeRequest');
		$this->CrudMock = $this->getMockBuilder('CrudComponent');
		$this->CollectionMock = $this->getMockBuilder('ComponentCollection');
	}

	public function tearDown() {
		parent::tearDown();

		unset(
			$this->ModelMock,
			$this->ActionMock,
			$this->RequestMock,
			$this->CrudMock
		);
	}

/**
 * Test that calling handle will invoke _handle
 *
 * @return void
 */
	public function testThatCrudActionWillHandle() {
		$Action = $this->ActionMock
			->disableOriginalConstructor()
			->setMethods(array('config', '_handle'))
			->getMock();
		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('index'));
		$Action
			->expects($this->once())
			->method('_handle');

		$CrudSubject = new CrudSubject(array(
			'action' => 'index',
			'model' => new StdClass(),
			'modelClass' => 'Blog',
			'args' => array()
		));

		$Action->handle($CrudSubject);
	}

/**
 * Returns a list of mocked classes that are related to the execution of the
 * action
 *
 * @return void
 */
	protected function _mockClasses($controllerClass = 'Controller') {
		$Request = $this->RequestMock->getMock();
		$Crud = $this->CrudMock
			->disableOriginalConstructor()
			->setMethods(array('trigger'))
			->getMock();

		$Collection = $this->CollectionMock->getMock();

		$Paginator = $this->getMock('PaginatorComponent', array('paginate'), array($Collection));
		$Paginator->settings['findType'] = 'another';

		$Collection
			->expects($this->any())
			->method('load')
			->with('Paginator')
			->will($this->returnCallback(function($class, $settings) use ($Paginator) {
				$Paginator->settings = array_merge($Paginator->settings, (array)$settings);
				return $Paginator;
			}));

		$Controller = new $controllerClass($Request);
		$Controller->Components = $Collection;
		$Controller->Paginator = $Paginator;

		$CrudSubject = new CrudSubject(array(
			'crud' => $Crud,
			'request' => $Request,
			'controller' => $Controller,
			'action' => 'index',
			'action' => 'index',
			'model' => null,
			'modelClass' => null,
			'args' => array(),
			'paginator' => $Paginator
		));

		$Crud
			->expects($this->at(0))
			->method('trigger')
			->with('beforePaginate', array('paginator' => $Paginator))
			->will($this->returnValue($CrudSubject));

		$Action = $this->ActionMock
			->setConstructorArgs(array($CrudSubject))
			->setMethods(array('enabled', 'config'))
			->getMock();
		$Action
			->expects($this->at(0))
			->method('config')
			->with('enabled')
			->will($this->returnValue(true));
		$Action
			->expects($this->at(1))
			->method('config')
			->with('action')
			->will($this->returnValue('index'));

		return compact('Request', 'Crud', 'Collection', 'Paginator', 'Controller', 'CrudSubject', 'Action');
	}

/**
 * Tests that calling index action will paginate the main model
 *
 * @return void
 */
	public function testIndexAction() {
		extract($this->_mockClasses());

		$Paginator
			->expects($this->once())
			->method('paginate')
			->will($this->returnValue(array('foo', 'bar')));

		$CrudSubject->items = array('foo', 'bar');
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterPaginate', array('items' => array('foo', 'bar')))
			->will($this->returnValue($CrudSubject));

		$Crud
			->expects($this->at(2))
			->method('trigger')
			->with('beforeRender');

		$Action->handle($CrudSubject);

		$expected = array(
			'page' => 1,
			'limit' => 20,
			'maxLimit' => 100,
			'paramType' => 'named',
			'findType' => 'another'
		);
		$this->assertEquals($expected, $Paginator->settings);
		$Controller->viewVars['items'] = array('foo', 'bar');
		$Controller->viewVars['success'] = true;
	}

/**
 * Tests that iterators are casted to arrays
 *
 * @return void
 */
	public function testPaginatorReturningIterator() {
		extract($this->_mockClasses());

		$iterator = new ArrayIterator(array('foo', 'bar'));
		$Paginator
			->expects($this->once())
			->method('paginate')
			->will($this->returnValue($iterator));

		$CrudSubject->items = $iterator;
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterPaginate', array('items' => $iterator))
			->will($this->returnValue($CrudSubject));

		$Crud->expects($this->at(2))->method('trigger')->with('beforeRender');

		$Action->handle($CrudSubject);
		$expected = array(
			'page' => 1,
			'limit' => 20,
			'maxLimit' => 100,
			'paramType' => 'named',
			'findType' => 'another'
		);
		$this->assertEquals($expected, $Paginator->settings);
		$Controller->viewVars['items'] = array('foo', 'bar');
		$Controller->viewVars['success'] = true;
	}

/**
 * Tests that $controller->paginate is copied to Paginator->settings
 *
 * @return void
 */
	public function testPaginateSettingsAreMerged() {
		extract($this->_mockClasses('TestController'));

		$Controller->paginate = array(
			'limit' => 50,
			'paramType' => 'querystring'
		);

		$Paginator->settings['maxLimit'] = 70;

		$Paginator
			->expects($this->once())
			->method('paginate')
			->will($this->returnValue(array('foo', 'bar')));

		$CrudSubject->items = array('foo', 'bar');
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterPaginate', array('items' => array('foo', 'bar')))
			->will($this->returnValue($CrudSubject));

		$Crud
			->expects($this->at(2))
			->method('trigger')
			->with('beforeRender');

		$Action->handle($CrudSubject);
		$expected = array(
			'limit' => 50,
			'maxLimit' => 70,
			'paramType' => 'querystring',
			'findType' => 'another',
			'page' => 1
		);

		$this->assertEquals($expected, $Paginator->settings);
		$Controller->viewVars['items'] = array('foo', 'bar');
		$Controller->viewVars['success'] = true;
	}

/**
 * Tests that paginate settings are set in the correct sub key
 *
 * @return void
 */
	public function testPaginateSettingsAreMergedCorrectKey() {
		extract($this->_mockClasses('TestController'));

		$CrudSubject->modelClass = 'MyModel';
		$Paginator->settings['MyModel'] = array(
			'limit' => 5,
			'findType' => 'another'
		);

		$Paginator
			->expects($this->once())
			->method('paginate')
			->will($this->returnValue(array('foo', 'bar')));

		$CrudSubject->items = array('foo', 'bar');
		$Crud
			->expects($this->at(1))
			->method('trigger')
			->with('afterPaginate', array('items' => array('foo', 'bar')))
			->will($this->returnValue($CrudSubject));

		$Crud
			->expects($this->at(2))
			->method('trigger')
			->with('beforeRender');

		$Action->handle($CrudSubject);
		$expected = array(
			'limit' => 5,
			'findType' => 'another'
		);

		$this->assertEquals($expected, $Paginator->settings['MyModel']);
		$Controller->viewVars['items'] = array('foo', 'bar');
		$Controller->viewVars['success'] = true;
	}

}
