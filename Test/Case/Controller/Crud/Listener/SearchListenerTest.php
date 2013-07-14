<?php

App::uses('Controller', 'Controller');
App::uses('CakeEvent', 'Event');
App::uses('CakeRequest', 'Network');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('SearchListener', 'Crud.Controller/Crud/Listener');

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class SearchListenerTest extends CakeTestCase {

	public function setup() {
		parent::setup();

		if (!CakePlugin::loaded('Search')) {
			CakePlugin::load('Search');
		}

	}

/**
 * Test implemented events
 *
 * @return void
 */
	public function testImplementedEvents() {
		$Instance = new SearchListener(new CrudSubject());
		$result = $Instance->implementedEvents();
		$expected = array('Crud.beforePaginate' => array('callable' => 'beforePaginate', 'priority' => 50));
		$this->assertEqual($result, $expected);
	}

/**
 * Test that CakeDC/Search is loaded
 *
 * @return void
 */
	public function testSearchPluginIsLoaded() {
		$bool = CakePlugin::loaded('Search');
		$this->assertTrue($bool, "CakeDC/Search is not installed");
	}

/**
 * Test that scope returns instance of it self for chaining
 *
 * @return void
 */
	public function testScopeReturnsSelf() {
		$Instance = new SearchListener(new CrudSubject());
		$result = $Instance->scope('test', array('key' => 'value'));
		$this->assertTrue($Instance === $result);
	}

/**
 * Test that scope without filter works
 *
 * @return void
 */
	public function testScopeWithoutFilter() {
		$Instance = new SearchListener(new CrudSubject());
		$Instance->scope('test', array('key' => 'value'));

		$expected = array('query' => array('key' => 'value'), 'filter' => null);
		$result = $Instance->config('scope.test');
		$this->assertEqual($result, $expected);
	}

/**
 * Test that scope with filter works
 *
 * @return void
 */
	public function testScopeWithFilter() {
		$Instance = new SearchListener(new CrudSubject());
		$Instance->scope('test', array('key' => 'value'), array('epic' => 'value'));

		$expected = array('query' => array('key' => 'value'), 'filter' => array('epic' => 'value'));
		$result = $Instance->config('scope.test');
		$this->assertEqual($result, $expected);
	}

/**
 * Test beforePaginate
 *
 * All clean, no configuration and nothing loaded
 *
 * @return void
 */
	public function testBeforePaginate() {
		$Model = new Model();
		$Request = new CakeRequest();
		$Controller = new Controller();
		$CrudSubject = new CrudSubject(array(
			'request' => $Request,
			'controller' => $Controller,
			'model' => $Model
		));

		$mocked = array(
			'_checkRequiredPlugin',
			'_ensureComponent',
			'_ensureBehavior',
			'_commonProcess',
			'_setFilterArgs',
			'_setPaginationOptions'
		);

		$Instance = $this->getMock('SearchListener', $mocked, array($CrudSubject));
		$Instance->expects($this->once())->method('_checkRequiredPlugin');
		$Instance->expects($this->once())->method('_ensureComponent')->with($Controller);
		$Instance->expects($this->once())->method('_ensureBehavior')->with($Model);
		$Instance->expects($this->once())->method('_commonProcess')->with($Controller, 'Model');
		$Instance->expects($this->once())->method('_setFilterArgs')->with($Model, array());
		$Instance->expects($this->once())->method('_setPaginationOptions')->with($Controller, $Model, array());

		$Instance->beforePaginate(new CakeEvent('beforePaginate', $CrudSubject));
	}

/**
 * Test beforePaginate
 *
 * All clean, no configuration and nothing loaded
 *
 * @return void
 */
	public function testBeforePaginateWithModelFilterArgs() {
		$Model = new Model();
		$Model->filterArgs = array('sample' => 'test');
		$Request = new CakeRequest();
		$Controller = new Controller();
		$CrudSubject = new CrudSubject(array(
			'request' => $Request,
			'controller' => $Controller,
			'model' => $Model
		));

		$mocked = array(
			'_checkRequiredPlugin',
			'_ensureComponent',
			'_ensureBehavior',
			'_commonProcess',
			'_setFilterArgs',
			'_setPaginationOptions'
		);

		$Instance = $this->getMock('SearchListener', $mocked, array($CrudSubject));
		$Instance->expects($this->once())->method('_checkRequiredPlugin');
		$Instance->expects($this->once())->method('_ensureComponent')->with($Controller);
		$Instance->expects($this->once())->method('_ensureBehavior')->with($Model);
		$Instance->expects($this->once())->method('_commonProcess')->with($Controller, 'Model');
		$Instance->expects($this->never())->method('_setFilterArgs', 'Should not be called when model got filterArgs already');
		$Instance->expects($this->once())->method('_setPaginationOptions')->with($Controller, $Model, array());

		$Instance->beforePaginate(new CakeEvent('beforePaginate', $CrudSubject));
	}

/**
 * Test that _checkRequiredPlugin doesn't throw an exception
 *
 * @return void
 */
	public function testCheckRequiredPlugins() {
		$Model = new Model();
		$Model->filterArgs = array('sample' => 'test');
		$Request = new CakeRequest();
		$Controller = new Controller();
		$CrudSubject = new CrudSubject(array(
			'request' => $Request,
			'controller' => $Controller,
			'model' => $Model
		));

		$mocked = array(
			'_ensureComponent',
			'_ensureBehavior',
			'_commonProcess',
			'_setFilterArgs',
			'_setPaginationOptions'
		);

		$Instance = $this->getMock('SearchListener', $mocked, array($CrudSubject));
		$Instance->expects($this->once())->method('_ensureComponent')->with($Controller);
		$Instance->expects($this->once())->method('_ensureBehavior')->with($Model);
		$Instance->expects($this->once())->method('_commonProcess')->with($Controller, 'Model');
		$Instance->expects($this->never())->method('_setFilterArgs', 'Should not be called when model got filterArgs already');
		$Instance->expects($this->once())->method('_setPaginationOptions')->with($Controller, $Model, array());

		$Instance->beforePaginate(new CakeEvent('beforePaginate', $CrudSubject));
	}

/**
 * Test that _checkRequiredPlugin doesn't throw an exception
 *
 * @return void
 */
	public function testCheckRequiredPluginsWithoutPlugin() {
		CakePlugin::unload('Search');

		$this->setExpectedException(
			'CakeException',
			'SearchListener requires the CakeDC/search plugin. Please install it from https://github.com/CakeDC/search'
		);

		$Model = new Model();
		$Model->filterArgs = array('sample' => 'test');
		$Request = new CakeRequest();
		$Controller = new Controller();
		$CrudSubject = new CrudSubject(array(
			'request' => $Request,
			'controller' => $Controller,
			'model' => $Model
		));

		$mocked = array(
			'_ensureComponent',
			'_ensureBehavior',
			'_commonProcess',
			'_setFilterArgs',
			'_setPaginationOptions'
		);

		$Instance = $this->getMock('SearchListener', $mocked, array($CrudSubject));
		$Instance->expects($this->never())->method('_ensureComponent');
		$Instance->expects($this->never())->method('_ensureBehavior');
		$Instance->expects($this->never())->method('_commonProcess');
		$Instance->expects($this->never())->method('_setFilterArgs');
		$Instance->expects($this->never())->method('_setPaginationOptions');

		$Instance->beforePaginate(new CakeEvent('beforePaginate', $CrudSubject));
	}

}
