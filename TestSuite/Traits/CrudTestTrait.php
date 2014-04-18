<?php
namespace Crud\TestSuite\Traits;

use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;
use Crud\Event\Subject;

trait CrudTestTrait {

	protected $_subject;

	public function _subscribeToEvents(Controller $controller = null) {
		if ($controller === null) {
			$controller = $this->controller;
		}

		$controller->Crud->on('beforeRender', function($event) {
			$this->_subject = $event->subject;
		});

		$controller->Crud->on('beforeRedirect', function($event) {
			$this->_subject = $event->subject;
		});
	}

	public function getModel($class, $methods, $alias, $table) {
		$mock = $this->getMockBuilder($class)
			->setMethods($methods)
			->setConstructorArgs([['alias' => $alias, 'table' => $table]])
			->getMock();
		$mock->connection(ConnectionManager::get('test'));
		return $mock;
	}

	public function assertRedirect($expected, $actual = null) {
		if ($actual === null) {
			$actual = $this->controller;
		}

		if ($actual instanceof Controller) {
			$actual = $actual->response;
		}

		if ($actual instanceof Response) {
			$actual = $actual->location();
		}

		if (empty($actual)) {
			throw new \Exception('assertRedirect: Expected "actual" to be a non-empty string');
		}

		$this->assertEquals(
			$expected,
			$actual->location(),
			'Was not redirected to ' . $expected
		);
	}

	public function assertEvents($expected, $actual = null) {
		if ($actual === null) {
			$actual = $this->_subject;
		}

		if ($actual instanceof Event) {
			$actual = $actual->subject->getEvents();
		}

		if ($actual instanceof Subject) {
			$actual = $actual->getEvents();
		}

		if (empty($actual)) {
			throw new \Exception('assertEvents: Expected actual to be not-empty');
		}

		if (!is_array($actual)) {
			throw new \Exception('assertEvents: Expected actual to be an array');
		}

		foreach ($expected as &$key) {
			if (false !== strpos($key, '.')) {
				continue;
			}

			$key = 'Crud.' . $key;
		}

		$this->assertEquals($expected, $actual, 'Not all expected events was fired');
	}

}
