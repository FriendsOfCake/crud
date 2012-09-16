<?php
App::uses('CrudListener', 'Crud.Controller/Event');

/**
 * Search Listener
 *
 * Inject search conditions based on a get-argument search parameter
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Nodes ApS, 2013
 */
class SearchListener extends CrudListener {

/**
 * Returns a list of all events that will fire in the controller during it's lifecycle.
 * You can override this function to add you own listener callbacks
 *
 * @return array
 */
	public function implementedEvents() {
		$prefix = $this->_crud->config('eventPrefix');
		return array(
			$prefix . '.init' => array('callable' => 'init'),
			$prefix . '.beforePaginate' => array('callable' => 'beforePaginate')
		);
	}

/**
 * init
 *
 * Unless already defined - Setup a simple query condition using the model's display field
 * Unless alraedy defined - read the query argument from the request object
 *
 * @param CakeEvent $event
 */
	public function init(CakeEvent $event) {
		if (is_null($this->config('conditions'))) {
			$model = $event->subject->model;
			$conditions = array(
				$model->alias . '.' . $model->displayField . ' LIKE' => "{term}%"
			);
			$this->config('conditions', $conditions);
		}

		if (is_null($this->config('term')) && isset($event->subject->request->query['q'])) {
			$this->config('term', $event->subject->request->query['q']);
		}
	}

/**
 * beforePaginate
 *
 * Before paginating, inject conditions for the search term from config
 *
 * @param CakeEvent $event
 */
	public function beforePaginate(CakeEvent $event) {
		$term = $this->config('term');
		if (is_null($term)) {
			return;
		}

		$model = $event->subject->model;
		$alias = $model->alias;

		$paginator = $event->subject->Components->load('Paginator');
		if (isset($paginator->settings[$alias])) {
			$this->_addConditions($paginator->settings[$alias], $term, $model);
		} else {
			$this->_addConditions($paginator->settings, $term, $model);
		}
	}

/**
 * _addConditions
 *
 * Add a condition into the passed paginate array
 *
 * @param array $paginate
 * @param string $term
 * @param object $model
 */
	protected function _addConditions(&$paginate, $term, $model) {
		$conditions = $this->config('conditions');
		if (is_callable($conditions)) {
			$return = $conditions($paginate, $term, $model);
			if ($return) {
				$paginate = $return;
			}
			return;
		}

		$replace = array('term' => $this->config('term'));
		$options = array('before' => '{', 'after' => '}');
		if (is_array($conditions)) {
			foreach($conditions as &$val) {
				$val = String::insert($val, $replace, $options);
			}
		} else {
			$conditions = String::insert($conditions, $replace, $options);
		}

		if ($conditions) {
			$paginate['conditions'][] = $conditions;
		}
	}
}
