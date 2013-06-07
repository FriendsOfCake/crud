<?php

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ViewCrudActionTest extends CakeTestCase {

	public function setUp() {
		$this->skipIf(true);
		parent::setUp();
		$this->Translations = new TranslationsListener(new CrudSubject(array('crud' => new StdClass)));
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Translations);
	}


/**
 * testViewAction
 *
 * Make sure that there is a call to render the view template
 */
	public function testViewAction() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('view');

		$this->Crud->getAction('view')->config('validateId', 'notUuid');
		$id = 1;

		$this->Crud->executeAction('view', array($id));
	}

/**
 * Test if custom finders work in view
 *
 * @return void
 */
	public function testCustomFindViewPublished() {
		$CrudAction = $this->Crud->getAction('view');

		$CrudAction->config('validateId', 'integer');
		$CrudAction->findmethod('firstPublished');

		$this->Crud->executeAction('view', array(2));

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.recordNotFound', $events);
		$this->assertSame(false, $index, "Crud.recordNotFound event triggered");

		$index = array_search('Crud.beforeFind', $events);
		$this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterFind', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");
	}

/**
 * Test if custom finders work in view - part 2
 *
 * @return void
 */
	public function testCustomFindViewUnpublished() {
		$this->Crud->settings['validateId'] = 'integer';
		$this->Crud->mapFindMethod('view', 'firstUnpublished');
		$this->Crud->executeAction('view', array(2));

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.recordNotFound', $events);
		$this->assertNotSame(false, $index, "There was no Crud.recordNotFound event triggered");
	}

}
