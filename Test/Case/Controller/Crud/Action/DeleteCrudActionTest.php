<?php

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class DeleteCrudActionText extends CakeTestCase {

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
 * Test if custom finders work in delete
 *
 * @return void
 */
	public function testCustomFindDeletePublished() {
		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return true; }
		));

		$CrudAction = $this->Crud->getAction('delete');
		$CrudAction->config('validateId', 'integer');
		$CrudAction->findMethod('firstPublished');

		$this->Crud->executeAction('delete', array(2));

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.recordNotFound', $events);
		$this->assertSame(false, $index, "Crud.recordNotFound event triggered");

		$index = array_search('Crud.beforeDelete', $events);
		$this->assertNotSame(false, $index, "There was no Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterDelete', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterDelete event triggered");

		$count = $this->model->find('count');
		$this->assertEquals(2, $count);
	}

/**
 * Test if custom finders work in delete - part 2
 *
 * @return void
 */
	public function testCustomFindDeleteUnpublished() {
		$this->controller->request->addDetector('delete', array(
			'callback' => function() { return true; }
		));

		$this->Crud->settings['validateId'] = 'integer';
		$this->Crud->mapFindMethod('delete', 'firstUnpublished');
		$this->Crud->executeAction('delete', array(2));

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.recordNotFound', $events);
		$this->assertNotSame(false, $index, "Crud.recordNotFound event triggered");

		$index = array_search('Crud.beforeDelete', $events);
		$this->assertSame(false, $index, "Crud.beforeDelete event triggered");

		$index = array_search('Crud.afterDelete', $events);
		$this->assertSame(false, $index, "Crud.afterDelete event triggered");

		$count = $this->model->find('count');
		$this->assertEquals(3, $count);
	}

}
