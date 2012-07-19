<?php

App::uses('CakeEventListener', 'Event');

/**
 * Implements beforeRender event listener to set related models' lists to
 * the view
 *
 **/
class RelatedModelsListener implements CakeEventListener {

	/**
	 * CRUD component events name prefix
	 *
	 * @var string
	 */
	protected $_prefix;

	/**
	 * List of models to be fetched in beforeRenderEvent
	 *
	 * @var array
	 */
	protected $_models = array();

	/**
	 * Class constructor
	 *
	 * @param string $prefix CRUD component events name prefix
	 * @param array $models List of models to be fetched in beforeRenderEvent
	 * @return void
	 */
	public function __construct($prefix, $models) {
		$this->_models = $models;
	}

	/**
	 * List of events implemented by this class
	 *
	 * @return array
	 */
	public function implementedEvents() {
		return array($this->_prefix . 'beforeRender' => 'beforeRender');
	}

	/**
	 * Fetches related models' list and sets them to a variable for the view
	 * Lists are limited buy default to 200 items. Should you need more, attach
	 * an event listener for `beforeListRelated` event to modify the query
	 *
	 * @param CakeEvent
	 * @return void
	 */
	public function beforeRender($event) {
		$component = $event->subject->crud;
		$controller = $vent->subject->controller;
		foreach ($this->_models as $m) {
			$model = $this->_getModelInstance($m, $controller);
			$query = array('limit' => 200);

			$subject = $component->trigger('beforeListRelated', compact('model', 'query'));
			$query = $subject->query;
			$items = $intance->find('list', $query);

			$viewVar = Inflector::variable($instance->alias);
			$subject = $component->trigger('afterListRelated', compact('model', 'items', 'viewVar'));
			$this->set($subject->viewVar, $subject->items);
		}
	}

	/**
	 * Returns model instance based on its name
	 *
	 * @param string $model name of the model
	 * @param Controller $controller instance to do a first look on it
	 * @return Model
	 */
	protected function _getModelInstance($model, $controller) {
		$controllerModel = $controller->{$controller->modelClass};
		if (isset($controllerModel->{$model})) {
			return $controllerModel->{$model};
		}
		if (isset($controller->{$model}) && $controller->{$model} instanceOf Model) {
			return $controller->{$model};
		}
		return ClassRegistry::init($model);
	}

}