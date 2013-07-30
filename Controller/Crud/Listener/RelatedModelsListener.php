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
class RelatedModelsListener extends CrudListener {

/**
 * Gets the list of associated model lists to be fetched for an action
 *
 * @param string $action name of the action
 * @return array
 */
	public function models($action = null) {
		$settings = $this->_action($action)->config('relatedModels');
		if ($settings === true) {
			$model = $this->_model();
			return array_merge(
				$model->getAssociated('belongsTo'),
				$model->getAssociated('hasAndBelongsToMany')
			);
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
 *
 * @param CakeEvent
 * @return void
 */
	public function beforeRender(CakeEvent $event) {
		$controller = $this->_controller();
		$primaryModel = $this->_model();

		$models = $this->models();

		if (empty($models)) {
			return;
		}

		foreach ($models as $m) {
			$associationType = $this->_getAssociationType($m, $primaryModel);
			$model = $this->_getModelInstance($m, $primaryModel, $controller, $associationType);

			$isTree = false;
			$query = array();

			if ($associationType == 'belongsTo') {
				$query['conditions'] = $primaryModel->belongsTo[$m]['conditions'];
			}

			if ($model->Behaviors->attached('Tree')) {
				$isTree = true;
				$query = array(
					'keyPath' => null,
					'valuePath' => null,
					'spacer' => '_',
					'recursive' => $model->Behaviors->Tree->settings[$model->alias]['recursive']
				);

				if (empty($query['conditions'])) {
					$query['conditions'] = $model->Behaviors->Tree->settings[$model->alias]['scope'];
				}
			}

			$viewVar = Inflector::variable(Inflector::pluralize($model->alias));
			$subject = $this->_trigger('beforeRelatedModel', compact('model', 'query', 'viewVar'));

			// If the viewVar is already set, don't overwrite it
			if (array_key_exists($subject->viewVar, $controller->viewVars)) {
				continue;
			}

			$query = $subject->query;
			if ($isTree) {
				$items = $model->generateTreeList(
					$query['conditions'],
					$query['keyPath'],
					$query['valuePath'],
					$query['spacer'],
					$query['recursive']
				);
			} else {
				$items = $model->find('list', $query);
			}

			$subject = $this->_trigger('afterRelatedModel', compact('model', 'items', 'viewVar'));
			$controller->set($subject->viewVar, $subject->items);
		}
	}

/**
 * Returns model instance based on its name
 *
 * @param string $model name of the model
 * @param Model $controllerModel default model instance for controller
 * @param Controller $controller instance to do a first look on it
 * @param string $associationType Association types
 * @return Model
 */
	protected function _getModelInstance($model, $controllerModel, $controller, $associationType = null) {
		if (isset($controllerModel->{$model})) {
			return $controllerModel->{$model};
		}

		if (isset($controller->{$model}) && $controller->{$model} instanceOf Model) {
			return $controller->{$model};
		}

		if ($associationType && !empty($controllerModel->{$associationType}[$model]['className'])) {
			return ClassRegistry::init($controllerModel->{$associationType}[$model]['className']);
		}

		return ClassRegistry::init($model);
	}

/**
 * Returns model's association type with controller's model
 *
 * @param string $model name of the model
 * @param Model $controllerModel default model instance for controller
 * @return string|null Association type if found else null
 */
	protected function _getAssociationType($model, $controllerModel) {
		$associated = $controllerModel->getAssociated();
		return isset($associated[$model]) ? $associated[$model] : null;
	}

}
