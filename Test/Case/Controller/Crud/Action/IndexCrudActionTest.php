<?php

/**
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class IndexCrudActionText extends CakeTestCase {

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
 * testIndexAction
 *
 * Make sure that there is a call to render the index template
 */
	public function testIndexAction() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('index');

		$this->request->params['named']= array();

		$this->Crud->executeAction('index');

		$events = CakeEventManager::instance()->getLog();

		$index = array_search('Crud.afterPaginate', $events);
		$this->assertNotSame(false, $index, "There was no Crud.afterPaginate event triggered");
	}


/**
 * Tests on method for beforePaginateEvent
 *
 * @expectedException RuntimeException
 * @expectedExceptionMessage Crud.beforePaginate called
 * @return void
 */
	public function testOnBeforePaginateString() {
		$this->Crud->on('beforePaginate', function($event) {
			throw new RuntimeException($event->name() . ' called');
		});
		$this->Crud->executeAction('index');
	}

/**
 * Tests on method for afterPaginate
 *
 * @expectedException RuntimeException
 * @expectedExceptionMessage Crud.afterPaginate called
 * @return void
 */
	public function testOnAfterPaginateString() {
		$this->Crud->on('afterPaginate', function($event) {
			throw new RuntimeException($event->name() . ' called');
		});

		$this->Crud->executeAction('index');
	}

/**
 * Tests on method for afterPaginate with full event name
 *
 * @expectedException RuntimeException
 * @expectedExceptionMessage Crud.afterPaginate called
 * @return void
 */
	public function testOnAfterPaginateFullNameString() {
		$this->Crud->on('Crud.afterPaginate', function($event) {
			throw new RuntimeException($event->name() . ' called');
		});

		$this->Crud->executeAction('index');
	}

/**
 * Test on method for on() with multiple events
 *
 * @return void
 */
	public function testOnOnWithArraySimple() {
		$result = array();
		$this->Crud->on(array('beforePaginate', 'beforeRender'), function($event) use (&$result) {
			$result[] = $event->name() . ' called';
		});
		$this->Crud->executeAction('index');

		$expected = array('Crud.beforePaginate called', 'Crud.beforeRender called');
		$this->assertSame($expected, $result);
	}

/**
 * Test on method for on() with multiple events
 *
 * @return void
 */
	public function testOnOnWithArrayComplex() {
		$result = array();
		$this->Crud->on(array('Crud.beforePaginate', 'beforeRender'), function($event) use (&$result) {
			$result[] = $event->name() . ' called';
		});
		$this->Crud->executeAction('index');

		$expected = array('Crud.beforePaginate called', 'Crud.beforeRender called');
		$this->assertSame($expected, $result);
	}


/**
 * Test if mapActionView with array yields the expected result
 *
 * @return void
 */
	public function testMapActionViewWithArrayNewAction() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('index');

		$this->Crud->mapAction('show_all', 'index');
		$this->Crud->mapActionView(array('show_all' => 'index', 'index' => 'overview'));
		$this->Crud->executeAction('show_all');
	}

/**
 * Test if mapActionView with array yields the expected result
 *
 * @return void
 */
	public function testMapActionViewWithArrayIndexAction() {
		$this->controller
			->expects($this->once())
			->method('render')
			->with('overview');

		$this->Crud->mapAction('show_all', 'index');
		$this->Crud->mapActionView(array('show_all' => 'index', 'index' => 'overview'));
		$this->Crud->executeAction('index');
	}

/**
 * Test if custom finds are changed when re-mapped
 *
 * @return void
 */
	public function testCustomFindChanged() {
		$this->Crud->mapFindMethod('index', 'custom_find');
		$this->assertEquals('custom_find', $this->Crud->getAction('index')->findMethod());

		$this->Crud->mapFindMethod('index', 'all');
		$this->assertEquals('all', $this->Crud->getAction('index')->findMethod());
	}

/**
 * Test that the default pagination settings match, bot for 2.3 and < 2.2
 *
 * @return void
 */
	public function testCustomFindPaginationDefaultNoAlias() {
		$this->Crud->executeAction('index');

		$this->assertEquals('all', $this->controller->paginate[0]);
		$this->assertEquals('all', $this->controller->paginate['findType']);
	}

/**
 * Test that the default pagination settings match, bot for 2.3 and < 2.2
 *
 * @return void
 */
	public function testCustomFindPaginationDefaultWithAlias() {
		$this->controller->paginate = array(
			'CrudExample' => array(
				'order' => array('name' => 'desc')
			),
			'demo' => true
		);

		$this->Crud->executeAction('index');

		$this->assertTrue(empty($this->controller->paginate[0]));
		$this->assertTrue(empty($this->controller->paginate['findType']));
		$this->assertFalse(empty($this->controller->paginate['CrudExample']));
		$this->assertFalse(empty($this->controller->paginate['CrudExample'][0]));
		$this->assertFalse(empty($this->controller->paginate['CrudExample']['findType']));
		$this->assertEquals(array('order' => array('name' => 'desc'), 0 => 'all', 'findType' => 'all'), $this->controller->paginate['CrudExample']);
	}

/**
 * Test if custom pagination works - for published posts
 *
 * @return void
 */
	public function testCustomFindPaginationCustomPublished() {
		$this->Crud->mapFindMethod('index', 'published');
		$this->Crud->executeAction('index');
		$this->assertEquals('published', $this->controller->paginate[0]);
		$this->assertEquals('published', $this->controller->paginate['findType']);
		$this->assertEquals(3, sizeof($this->controller->viewVars['items']));
	}

/**
 * Test if custom pagination works - for unpublished posts
 *
 * @return void
 */
	public function testCustomFindPaginationCustomUnpublished() {
		$this->Crud->mapFindMethod('index', 'unpublished');
		$this->Crud->executeAction('index');
		$this->assertEquals('unpublished', $this->controller->paginate[0]);
		$this->assertEquals('unpublished', $this->controller->paginate['findType']);
		$this->assertEquals(0, sizeof($this->controller->viewVars['items']));
	}

/**
 * Test if custom pagination works when findType is changed from Controller
 * paginate property
 *
 * @return void
 */
	public function testCustomFindPaginationWithControllerFindMethod() {
		$this->controller->paginate = array('findType' => 'unpublished');
		$this->Crud->executeAction('index');
		$this->assertEquals('unpublished', $this->controller->paginate[0]);
		$this->assertEquals('unpublished', $this->controller->paginate['findType']);
		$this->assertEquals(0, sizeof($this->controller->viewVars['items']));
	}

	public function testIndexActionPaginationSettingsNotLost() {
		$this->Crud->executeAction('index');

		$paging = $this->controller->request['paging'];

		$this->assertSame(1, $paging['CrudExample']['page']);
		$this->assertSame(3, $paging['CrudExample']['current']);
		$this->assertSame(1000, $paging['CrudExample']['limit']);
	}

	public function testIndexActionPaginationSettingsCanBeOverwritten() {
		$this->controller->paginate = array('limit' => 11);

		$this->Crud->executeAction('index');

		$paging = $this->controller->request['paging'];

		$this->assertSame(1, $paging['CrudExample']['page']);
		$this->assertSame(3, $paging['CrudExample']['current']);
		$this->assertSame(11, $paging['CrudExample']['limit']);

		$this->assertSame(11, $this->controller->Components->load('Paginator')->settings['limit']);
	}

	public function testPersistDirectPaginatorSettingsWillNotBeCopied() {
		$Paginator = $this->controller->Components->load('Paginator');

		$Paginator->settings = array('limit' => 23);

		$this->Crud->executeAction('index');

		$paging = $this->controller->request['paging'];

		$this->assertSame(1, $paging['CrudExample']['page']);
		$this->assertSame(3, $paging['CrudExample']['current']);
		$this->assertSame(1000, $paging['CrudExample']['limit']);
		$this->assertNotSame(23, $Paginator->settings['limit']);
	}

	public function testOnBeforePaginateWithPaginatConditionsFromBeforePaginateCallback() {
		$Paginator = $this->controller->Components->load('Paginator');
		$this->Crud->on('beforePaginate', function($event) {
			$event->subject->controller->paginate['conditions'] = array('author_id' => 1);
		});

		$this->Crud->executeAction('index');

		$items = $this->controller->viewVars['items'];
		$this->assertSame(2, sizeof($items), 'beforePaginate needs to have an effect on the pagination');
		$this->assertEquals(array('author_id' => 1), $Paginator->settings['conditions']);
	}

	public function testOnBeforePaginateWithPaginatLimitFromBeforePaginateCallback() {
		$Paginator = $this->controller->Components->load('Paginator');
		$this->Crud->on('beforePaginate', function($event) {
			$event->subject->controller->paginate['limit'] = 99;
		});

		$this->Crud->executeAction('index');

		$this->assertEquals(99, $Paginator->settings['limit']);
	}

	public function testIfConditionsPersistetInIndexAction() {
		$Paginator = $this->controller->Components->load('Paginator');

		$this->controller->paginate = array('conditions' => array('1 = 2'));
		$this->Crud->executeAction('index');
		$this->assertSame(array('1 = 2'), $Paginator->settings['conditions']);

		$Paginator->settings = array('conditions' => array('2 = 3'));
		$this->Crud->executeAction('index');
		$this->assertSame(array('1 = 2'), $Paginator->settings['conditions'], "Pagination settings from controller should always trump Paginator->settings");

		$Paginator->settings = array('conditions' => array('2 = 3'));
		$this->controller->paginate = array();
		$this->Crud->executeAction('index');
		$this->assertSame(array('2 = 3'), $Paginator->settings['conditions']);
	}

	public function testPaginationWithIterator() {
		$this->controller->paginate = array('limit' => 10);

		$this->Crud->on('afterPaginate', function(CakeEvent $e) {
			$e->subject->items = new ArrayIterator($e->subject->items);
		});

		$this->Crud->executeAction('index');

		$this->assertNotEmpty($this->controller->viewVars);
		$this->assertNotEmpty($this->controller->viewVars['items']);
		$this->assertSame(3, sizeof($this->controller->viewVars['items']));

		$ids = Hash::extract($this->controller->viewVars['items'], '{n}.CrudExample.id');
		$this->assertEquals(array(1,2,3), $ids);
	}

}
