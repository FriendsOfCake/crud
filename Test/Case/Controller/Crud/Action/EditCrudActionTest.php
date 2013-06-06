<?php

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class EditCrudActionTest extends CakeTestCase {

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
 * Test if custom finders work in edit
 *
 * @return void
 */
	public function testCustomFindEditPublished() {
		$this->Crud->getAction('edit')->config('validateId', 'integer');
		$this->Crud->mapFindMethod('edit', 'firstPublished');
		$this->Crud->executeAction('edit', array(2));

		$this->assertNotEmpty($this->request->data);
		$this->assertNotEmpty($this->request->data['CrudExample']);
		$this->assertSame('2', (string)$this->request->data['CrudExample']['id']);
	}

/**
 * Test if custom finders work in edit - part two
 *
 * @return void
 */
	public function testCustomFindEditUnpublished() {
		$this->controller->expects($this->once())->method('redirect');

		$this->Crud->settings['validateId'] = 'integer';
		$this->Crud->mapFindMethod('edit', 'firstUnpublished');
		$this->Crud->executeAction('edit', array(2));

		$this->assertTrue(empty($this->request->data));

		$events = CakeEventManager::instance()->getLog();
		$index = array_search('Crud.recordNotFound', $events);
		$this->assertNotSame(false, $index, "There was no Crud.recordNotFound event triggered");
	}

}
