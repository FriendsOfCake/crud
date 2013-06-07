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
 * testEditActionGet
 *
 * Do we get a call to render the form template?
 */
	public function testEditActionGet() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('edit');

		$this->Crud->getAction('edit')->config('validateId', 'notUuid');
		$id = 1;

		$this->Crud->executeAction('edit', array($id));
	}

/**
 * testEditActionPost
 *
 * Simulating submitting a post form which just changes the title of the model
 * to the name of the method. Check the update is persisted to the db
 */
	public function testEditActionPost() {
		$this->controller
			->expects($this->never())
			->method('render');

		$this->controller->request->addDetector('put', array(
			'callback' => function() { return true; }
		));

		$this->controller->data = array(
			'CrudExample' => array(
				'id' => 1,
				'title' => __METHOD__
			)
		);

		$this->Crud->settings['validateId'] = 'notUuid';
		$id = 1;

		$this->Crud->executeAction('edit', array($id));

		$this->model->id = $id;
		$result = $this->model->field('title');
		$this->assertSame(__METHOD__, $result);

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.afterSave', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterSave event triggered");
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
