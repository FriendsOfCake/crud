<?php

App::uses('CrudListener', 'Crud.Controller/Crud');
App::uses('Inflector', 'Utility');

class ScaffoldListener extends CrudListener {

	public function beforeRender(CakeEvent $event) {
		$subject = $event->subject;
		$model = $subject->model;
		$request = $subject->request;
		$controller = $subject->controller;

		$this->ScaffoldModel = $model;
		$this->scaffoldTitle = Inflector::humanize(Inflector::underscore($controller->viewPath));
		$this->scaffoldActions = $controller->scaffold;
		$title = __d('cake', 'Scaffold :: ') . Inflector::humanize($request->action) . ' :: ' . $this->scaffoldTitle;

		$modelClass = $controller->modelClass;
		$primaryKey = $this->ScaffoldModel->primaryKey;
		$displayField = $this->ScaffoldModel->displayField;
		$singularVar = Inflector::variable($modelClass);
		$pluralVar = Inflector::variable($controller->name);
		$singularHumanName = Inflector::humanize(Inflector::underscore($modelClass));
		$pluralHumanName = Inflector::humanize(Inflector::underscore($controller->name));
		$scaffoldFields = array_keys($this->ScaffoldModel->schema());
		$associations = $this->_associations();

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
 * @return array Associations for model
 */
	protected function _associations() {
		$keys = array('belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany');
		$associations = array();

		foreach ($keys as $type) {
			foreach ($this->ScaffoldModel->{$type} as $assocKey => $assocData) {
				$associations[$type][$assocKey]['primaryKey'] =	$this->ScaffoldModel->{$assocKey}->primaryKey;
				$associations[$type][$assocKey]['displayField'] =	$this->ScaffoldModel->{$assocKey}->displayField;
				$associations[$type][$assocKey]['foreignKey'] =	$assocData['foreignKey'];

				list($plugin, $model) = pluginSplit($assocData['className']);
				if ($plugin) {
					$plugin = Inflector::underscore($plugin);
				}

				$associations[$type][$assocKey]['plugin'] = $plugin;
				$associations[$type][$assocKey]['controller'] =	Inflector::pluralize(Inflector::underscore($model));

				if ($type === 'hasAndBelongsToMany') {
					$associations[$type][$assocKey]['with'] = $assocData['with'];
				}
			}
		}

		return $associations;
	}

}
