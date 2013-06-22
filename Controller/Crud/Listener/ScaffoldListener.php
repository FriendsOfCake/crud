<?php

App::uses('CakeEvent', 'Event');
App::uses('CrudListener', 'Crud.Controller/Crud');

/**
 * Implements beforeRender event listener that uses the build-in
 * scaffolding views in cakephp.
 *
 * Using this listener you don't have to bake your views when
 * doing rapid prototyping of your application
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class ScaffoldListener extends CrudListener {

/**
 * List of events implemented by this class
 *
 * @return array
 */
	public function implementedEvents() {
		return array('Crud.beforeRender' => 'beforeRender');
	}

/**
 * Do all the magic needed for using the
 * cakephp scaffold views
 *
 * @param CakeEvent
 * @return void
 */
	public function beforeRender(CakeEvent $event) {
		$subject = $event->subject;
		$model = $subject->model;
		$request = $subject->request;
		$controller = $subject->controller;

		$scaffoldTitle = Inflector::humanize(Inflector::underscore($controller->viewPath));
		$title = __d('cake', 'Scaffold :: ') . Inflector::humanize($request->action) . ' :: ' . $scaffoldTitle;

		$modelClass = $controller->modelClass;
		$primaryKey = $model->primaryKey;
		$displayField = $model->displayField;
		$singularVar = Inflector::variable($modelClass);
		$pluralVar = Inflector::variable($controller->name);
		$singularHumanName = Inflector::humanize(Inflector::underscore($modelClass));
		$pluralHumanName = Inflector::humanize(Inflector::underscore($controller->name));
		$scaffoldFields = array_keys($model->schema());
		$associations = $this->_associations($model);

		$controller->set(compact(
			'modelClass', 'primaryKey', 'displayField', 'singularVar', 'pluralVar',
			'singularHumanName', 'pluralHumanName', 'scaffoldFields', 'associations'
		));
		$controller->set('title_for_layout', $title);

		if ($controller->viewClass) {
			$controller->viewClass = 'Scaffold';
		}
	}

/**
 * Returns associations for controllers models.
 *
 * @param Model $model
 * @return array Associations for model
 */
	protected function _associations(Model $model) {
		$associations = array();
		$keys = array('belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany');

		$associated = $model->getAssociated();
		foreach ($associated as $assocKey => $type) {
			$associations[$type] = array();

			$assocDataAll = $model->$type;

			$assocData = $assocDataAll[$assocKey];
			$associatedModel = $model->{$assocKey};

			$associations[$type][$assocKey]['primaryKey'] =	$associatedModel->primaryKey;
			$associations[$type][$assocKey]['displayField'] =	$associatedModel->displayField;
			$associations[$type][$assocKey]['foreignKey'] =	$assocData['foreignKey'];

			list($plugin, $modelClass) = pluginSplit($assocData['className']);

			if ($plugin) {
				$plugin = Inflector::underscore($plugin);
			}

			$associations[$type][$assocKey]['plugin'] = $plugin;
			$associations[$type][$assocKey]['controller'] =	Inflector::pluralize(Inflector::underscore($modelClass));

			if ($type === 'hasAndBelongsToMany') {
				$associations[$type][$assocKey]['with'] = $assocData['with'];
			}
		}

		return $associations;
	}

}
