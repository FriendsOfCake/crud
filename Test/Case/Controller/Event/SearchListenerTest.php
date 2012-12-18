<?php

App::uses('AppController', 'Controller');
App::uses('Controller', 'Controller');
App::uses('CakeEvent', 'Event');
App::uses('SearchListener', 'Crud.Controller/Event');
App::uses('ControllerTestCase', 'TestSuite');
App::uses('Icecream', 'Frisko.Model');
App::uses('CrudSubject', 'Crud.Controller/Event');
App::uses('CrudComponent', 'Crud.Controller/Component');
App::uses('ComponentCollection', 'Controller');

class SearchListenerTestController extends Controller {

	public $components = array('Crud.Crud');
}

class SearchListenerTest extends ControllerTestCase {

	public function setUp() {
		$subject = new CrudSubject();
		$subject->crud = new CrudComponent(new ComponentCollection());
		$this->SearchListener = new SearchListener($subject);

		parent::setUp();
	}

	public function tearDown() {
		unset($this->SearchListener);

		parent::tearDown();
	}

	public function testCanOverrideConditions() {
		$override = array(
			'conditions' => array(
				'Some.model' => '%{term}%'
			)
		);
		$config = $this->SearchListener->config($override);

		$expected = array(
			'conditions' => array(
				'Some.model' => '%{term}%'
			)
		);
		$this->assertEquals($expected, $config);
	}

	public function testCanOverrideConfigSearchTerm() {
		$override = array(
			'term' => 'abc'
		);
		$config = $this->SearchListener->config($override);

		$expected = array(
			'term' => 'abc'
		);
		$this->assertEquals($expected, $config);
	}

	public function testInititializeConditions() {
		$subject = $this->generate('SearchListenerTest', array());
		$subject->model = ClassRegistry::init(array(
			'class' => 'Post',
			'name' => 'Post',
			'table' => false
		));
		$subject->model->displayField = 'title';

		$Event = new CakeEvent('Crud.init', $subject);

		$this->SearchListener->init($Event);

		$expected = array(
			'conditions' => array(
				'Post.title LIKE' => '{term}%'
			)
		);

		$actual = $this->SearchListener->config();
		$this->assertSame($expected, $actual);
	}

	public function testInititializeFromRequest() {
		$subject = $this->generate('SearchListenerTest', array());
		$subject->model = ClassRegistry::init(array(
			'class' => 'Post',
			'name' => 'Post',
			'table' => false
		));
		$subject->model->displayField = 'title';
		$subject->request = new CakeRequest('/?q=crud');

		$Event = new CakeEvent('Crud.init', $subject);

		$this->SearchListener->init($Event);

		$expected = array(
			'conditions' => array(
				'Post.title LIKE' => '{term}%'
			),
			'term' => 'crud'
		);

		$actual = $this->SearchListener->config();
		$this->assertSame($expected, $actual);
	}

	public function testBeforePaginateBasic() {
		$this->SearchListener->config('term', 'foo');

		$subject = $this->generate('SearchListenerTest', array());
		$subject->model = ClassRegistry::init(array(
			'class' => 'Post',
			'name' => 'Post',
			'table' => false
		));
		$subject->model->displayField = 'title';

		$Event = new CakeEvent('Crud.init', $subject);
		$this->SearchListener->init($Event);
		$Event = new CakeEvent('Crud.beforePaginate', $subject);
		$this->SearchListener->beforePaginate($Event);

		$expected = array(
			array(
				'Post.title LIKE' => 'foo%'
			)
		);
		$actual = $subject->Components->load('Paginator')->settings['conditions'];
		$this->assertSame($expected, $actual);
	}

	public function testBeforePaginateNoClobber() {
		$this->SearchListener->config('term', 'foo');

		$subject = $this->generate('SearchListenerTest', array());
		$subject->model = ClassRegistry::init(array(
			'class' => 'Post',
			'name' => 'Post',
			'table' => false
		));
		$subject->model->displayField = 'title';
		$subject->Components->load('Paginator')->settings['conditions'][] = array(
			'Post.title LIKE' => '%something%'
		);

		$Event = new CakeEvent('Crud.init', $subject);
		$this->SearchListener->init($Event);
		$Event = new CakeEvent('Crud.beforePaginate', $subject);
		$this->SearchListener->beforePaginate($Event);

		$expected = array(
			array(
				'Post.title LIKE' => '%something%'
			),
			array(
				'Post.title LIKE' => 'foo%'
			)
		);
		$actual = $subject->Components->load('Paginator')->settings['conditions'];
		$this->assertSame($expected, $actual);
	}

	public function testBeforePaginateClosure() {
		$subject = $this->generate('SearchListenerTest', array());
		$subject->model = ClassRegistry::init(array(
			'class' => 'Post',
			'name' => 'Post',
			'table' => false
		));
		$subject->model->displayField = 'title';

		$this->SearchListener->config('conditions', function($paginate, $term, $model) {
			$paginate['conditions'][] = "anything at all with '$term' in it";
			return $paginate;
		});
		$this->SearchListener->config('term', 'abc');

		$Event = new CakeEvent('Crud.init', $subject);
		$this->SearchListener->init($Event);
		$Event = new CakeEvent('Crud.beforePaginate', $subject);
		$this->SearchListener->beforePaginate($Event);

		$expected = array(
			'page' => 1,
			'limit' => 20,
			'maxLimit' => 100,
			'paramType' => 'named',
			'conditions' => array(
				'anything at all with \'abc\' in it'
			)
		);
		$actual = $subject->Components->load('Paginator')->settings;
		$this->assertSame($expected, $actual);
	}
}
