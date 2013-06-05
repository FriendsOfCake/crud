<?php

App::uses('CakeEventListener', 'Event');
App::uses('CrudListener', 'Crud.Controller/Crud');
App::uses('CrudSubject', 'Crud.Controller/Crud');

/**
 * Implements beforeRender event listener to set related models' lists to
 * the view
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class RelatedModelsListener extends CrudListener implements CakeEventListener {

/**
 * Enables association list fetching for specified actions.
 *
 * @param string|array $actions list of action names to enable
 * @return void
 */
	public function enable($actions) {
		if (!is_array($actions)) {
			$actions = array($actions);
		}

		foreach ($actions as $action) {
			$actionClass = $this->_crud->getAction($action);
			$config = $actionClass->config('relatedLists');
			if (empty($config)) {
				$actionClass->config('relatedLists', true);
			}
		}
	}

/**
 * Sets the list of model relationships to be fetched as lists for an action
 *
 * @param array|boolean $models list of model association names to be fetch on $action
 *  if `true`, list of models will be constructed out of associated models of main controller's model
 * @param string $action name of the action to apply this rule to. If left null then
 *  it will use the current controller action
 * @return void
 */
	public function map($models, $action = null) {
		if (empty($action)) {
			$action = $this->_subject->action;
		}

		if (is_string($models)) {
			$models = array($models);
		}

		$this->_crud->getAction($action)->config('relatedLists', $models);
	}

/**
 * Gets the list of associated model lists to be fetched for an action
 *
 * @param string $action name of the action
 * @return array
 */
	public function models($action = null) {
		$actionClass = $this->_crud->getAction($action);

		$settings = $actionClass->config('relatedLists');
		if ($settings === true) {
			return array_keys($this->_subject->model->getAssociated());
		}

		if (empty($settings)) {
			return array();
		}

		if (isset($settings['default'])) {
			if (false === $settings['default']) {
				return array();
			}

			if (is_array($settings['default'])) {
				return $settings['default'];
			}
		}

		if ($settings !== true) {
			return $settings;
		}

		return array_keys($this->_subject->model->getAssociated());
	}

/**
 * List of events implemented by this class
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			$this->_crud->config('eventPrefix') . '.init' => 'init',
			$this->_crud->config('eventPrefix') . '.beforeRender' => 'beforeRender'
		);
	}

/**
 * Fetches related models' list and sets them to a variable for the view
 * Lists are limited buy default to 200 items. Should you need more, attach
 * an event listener for `beforeListRelated` event to modify the query
 *
 * @param CakeEvent
 * @return void
 */
	public function beforeRender(CakeEvent $event) {
		$component = $event->subject->crud;
		$controller = $event->subject->controller;
		$models = $this->models();

		if (empty($models)) {
			return;
		}

		foreach ($models as $m) {
			$model = $this->_getModelInstance($m, $event->subject->model, $controller);
			$query = array('limit' => 200);

			$viewVar = Inflector::variable(Inflector::pluralize($model->alias));
			$subject = $component->trigger('beforeListRelated', compact('model', 'query', 'viewVar'));

			// If the viewVar is already set, don't overwrite it
			if (array_key_exists($subject->viewVar, $controller->viewVars)) {
				continue;
			}

			$query = $subject->query;
			$items = $model->find('list', $query);

			$subject = $component->trigger('afterListRelated', compact('model', 'items', 'viewVar'));
			$controller->set($subject->viewVar, $subject->items);
		}
	}

/**
 * Returns model instance based on its name
 *
 * @param string $model name of the model
 * @param Model $controllerModel default model instance for controller
 * @param Controller $controller instance to do a first look on it
 * @return Model
 */
	protected function _getModelInstance($model, $controllerModel, $controller) {
		if (isset($controllerModel->{$model})) {
			return $controllerModel->{$model};
		}

		if (isset($controller->{$model}) && $controller->{$model} instanceOf Model) {
			return $controller->{$model};
		}

		return ClassRegistry::init($model);
	}

}
