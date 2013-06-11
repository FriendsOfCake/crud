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
 * Gets the list of associated model lists to be fetched for an action
 *
 * @param string $action name of the action
 * @return array
 */
	public function models($action = null) {
		$actionClass = $this->_crud->action($action);

		$settings = $actionClass->config('relatedModels');
		if (is_null($settings) || $settings === true) {
			return array_keys($this->_subject->model->getAssociated());
		}

		if (empty($settings)) {
			return array();
		}

		if (is_string($settings)) {
			$settings = array($settings);
		}

		return $settings;
	}

/**
 * List of events implemented by this class
 *
 * @return array
 */
	public function implementedEvents() {
		return array('Crud.beforeRender' => 'beforeRender');
	}

/**
 * Fetches related models' list and sets them to a variable for the view
 * Lists are limited buy default to 200 items. Should you need more, attach
 * an event listener for `beforeRelatedModel` event to modify the query
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
			$subject = $component->trigger('beforeRelatedModel', compact('model', 'query', 'viewVar'));

			// If the viewVar is already set, don't overwrite it
			if (array_key_exists($subject->viewVar, $controller->viewVars)) {
				continue;
			}

			$query = $subject->query;
			$items = $model->find('list', $query);

			$subject = $component->trigger('afterRelatedModel', compact('model', 'items', 'viewVar'));
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
