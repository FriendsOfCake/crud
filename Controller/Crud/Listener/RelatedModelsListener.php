<?php

App::uses('CakeEventListener', 'Event');
App::uses('CrudListener', 'Crud.Controller/Crud');

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
			$ModelInstance = $this->_model();
			return array_merge(
				$ModelInstance->getAssociated('belongsTo'),
				$ModelInstance->getAssociated('hasAndBelongsToMany')
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
 * Find and publish all related models to the view
 * for an action
 *
 * @param NULL|string $action If NULL the current action will be used
 * @return void
 */
	public function publishRelatedModels($action = null) {
		$models = $this->models($action);

		if (empty($models)) {
			return;
		}

		$Controller = $this->_controller();

		foreach ($models as $model) {
			$associationType = $this->_getAssociationType($model);
			$AssociatedModel = $this->_getModelInstance($model, $associationType);

			$viewVar = Inflector::variable(Inflector::pluralize($AssociatedModel->alias));
			if (array_key_exists($subject->viewVar, $Controller->viewVars)) {
				continue;
			}

			$query = $this->_getQuery($AssociatedModel, $associationType);

			$subject = $this->_trigger('beforeRelatedModel', compact('model', 'query', 'viewVar'));
			$items = $this->_findRelatedItems($AssociatedModel, $subject->query);
			$subject = $this->_trigger('afterRelatedModel', compact('model', 'items', 'viewVar'));

			$Controller->set($subject->viewVar, $subject->items);
		}
	}

/**
 * Fetches related models' list and sets them to a variable for the view
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforeRender(CakeEvent $event) {
		$this->publishRelatedModels();
	}

/**
 * Execute the DB query to find the related items
 *
 * @param Model $Model
 * @param array $query
 * @return array
 */
	protected function _findRelatedItems(Model $Model, $query) {
		if ($this->_hasTreeBehavior($Model)) {
			return $ModelInstance->generateTreeList(
				$query['conditions'],
				$query['keyPath'],
				$query['valuePath'],
				$query['spacer'],
				$query['recursive']
			);
		}

		return $ModelInstance->find('list', $query);
	}

/**
 * Check if a model has the Tree behavior attached or not
 *
 * @param Model $Model
 * @return boolean
 */
	protected function _hasTreeBehavior(Model $Model) {
		return $Model->Behaviors->attached('Tree');
	}

/**
 * Get the query to find the related items for an associated model
 *
 * @param Model $AssociatedModel
 * @param Model $PrimaryModel
 * @param string $associationType
 * @return array
 */
	protected function _getQuery(Model $AssociatedModel, Model $PrimaryModel, $associationType) {
		$query = array();

		if ($associationType === 'belongsTo') {
			$query['conditions'] = $PrimaryModel->belongsTo[$AssociatedModel->alias]['conditions'];
		}

		if ($this->_hasTreeBehavior($AssociatedModel)) {
			$query = array(
				'keyPath' => null,
				'valuePath' => null,
				'spacer' => '_',
				'recursive' => $AssociatedModel->Behaviors->Tree->settings[$AssociatedModel->alias]['recursive']
			);

			if (empty($query['conditions'])) {
				$query['conditions'] = $AssociatedModel->Behaviors->Tree->settings[$AssociatedModel->alias]['scope'];
			}
		}

		return $query;
	}

/**
 * Returns model instance based on its name
 *
 * @param string $modelName
 * @param string $associationType
 * @return Model
 */
	protected function _getModelInstance($modelName, $associationType = null) {
		$PrimaryModel = $this->_model();

		if (isset($PrimaryModel->{$modelName})) {
			return $PrimaryModel->{$modelName};
		}

		$Controller = $this->_controller();
		if (isset($Controller->{$modelName}) && $Controller->{$modelName} instanceOf Model) {
			return $Controller->{$modelName};
		}

		if ($associationType && !empty($PrimaryModel->{$associationType}[$modelName]['className'])) {
			return ClassRegistry::init($PrimaryModel->{$associationType}[$modelName]['className']);
		}

		return ClassRegistry::init($modelName);
	}

/**
 * Returns model's association type with controller's model
 *
 * @param string $modelName
 * @return string|null Association type if found else null
 */
	protected function _getAssociationType($modelName) {
		$associated = $this->_model()->getAssociated();
		return isset($associated[$modelName]) ? $associated[$modelName] : null;
	}

}
