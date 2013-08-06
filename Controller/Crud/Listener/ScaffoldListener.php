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
 * For full copyright and license information, please see the LICENSE.txt
 */
class ScaffoldListener extends CrudListener {

/**
 * List of events implemented by this class
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.beforeRender' => 'beforeRender',
			'Crud.beforeFind' => 'beforeFind',
			'Crud.beforePaginate' => 'beforePaginate'
		);
	}

/**
 * Make sure to contain associated models
 *
 * This have no effect on clean applications where containable isn't
 * loaded, but for those who does have it loaded, we should
 * use it.
 *
 * This help applications with `$recursive -1` in their AppModel
 * and containable behavior loaded
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforeFind(CakeEvent $event) {
		if (!isset($event->subject->query['contain'])) {
			$event->subject->query['contain'] = array();
		}

		$existing = $event->subject->query['contain'];
		$associated = array_keys($this->_model()->getAssociated());

		$event->subject->query['contain'] = array_merge($existing, $associated);
	}

/**
 * Make sure to contain associated models
 *
 * This have no effect on clean applications where containable isn't
 * loaded, but for those who does have it loaded, we should
 * use it.
 *
 * This help applications with `$recursive -1` in their AppModel
 * and containable behavior loaded
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforePaginate(CakeEvent $event) {
		$Paginator = $this->_controller()->Paginator;

		if (!isset($Paginator->settings['contain'])) {
			$Paginator->settings['contain'] = array();
		}

		$existing = $Paginator->settings['contain'];
		$associated = array_keys($this->_model()->getAssociated());

		$Paginator->settings['contain'] = array_merge($existing, $associated);
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
		$model = $this->_model();
		$request = $this->_request();
		$controller = $this->_controller();
		$action = $request->action;

		$scaffoldTitle = Inflector::humanize(Inflector::underscore($controller->viewPath));
		$title = $scaffoldTitle . ' :: ' . Inflector::humanize($action);

		$modelClass = $controller->modelClass;
		$primaryKey = $model->primaryKey;
		$displayField = $model->displayField;
		$singularVar = Inflector::variable($modelClass);
		$pluralVar = Inflector::variable($controller->name);
		$singularHumanName = Inflector::humanize(Inflector::underscore($modelClass));
		$pluralHumanName = Inflector::humanize(Inflector::underscore($controller->name));
		$modelSchema = $model->schema();
		$associations = $this->_associations($model);
		$scaffoldFilters = $this->_scaffoldFilters($request);
		$sidebarActions = $this->_sidebarActions();
		$scaffoldRelatedActions = $this->_scaffoldRelatedActions();
		$adminTitle = $this->_adminTitle();

		$_sort = $this->_action()->config('scaffoldFields');
		$_sort = empty($_sort);
		$scaffoldFields = $this->_scaffoldFields($_sort);
		$scaffoldFields = $this->_scaffoldFieldExclude($scaffoldFields, $_sort);

		$controller->set(compact(
			'modelClass', 'primaryKey', 'displayField', 'singularVar', 'pluralVar',
			'singularHumanName', 'pluralHumanName', 'scaffoldFields', 'associations',
			'scaffoldFilters', 'action', 'modelSchema', 'sidebarActions',
			'scaffoldRelatedActions', 'adminTitle'
		));

		$controller->set('title_for_layout', $title);

		if ($controller->viewClass) {
			$controller->viewClass = 'Scaffold';
		}

		$controller->helpers = (array)$controller->helpers;
		$controller->helpers[] = 'Time';

		$controller->layout = 'Crud.default';

		App::build(array(
			'View' => array(
				APP . 'View' . DS,
				APP . 'Plugin' . DS . 'Crud' . DS . 'View' . DS
			)
		));
	}

	protected function _adminTitle() {
		$adminTitle = $this->_action()->config('adminTitle');
		if (empty($adminTitle)) {
			$adminTitle = 'Admin';
		}

		return $adminTitle;
	}

	protected function _scaffoldRelatedActions() {
		$scaffoldRelatedActions = $this->_action()->config('scaffoldRelatedActions');
		if ($scaffoldRelatedActions === null) {
			$scaffoldRelatedActions = true;
		} else {
			$scaffoldRelatedActions = (bool)$scaffoldRelatedActions;
		}
		return $scaffoldRelatedActions;
	}

/**
 * Returns fields to be displayed on scaffolded view
 *
 * @param boolean $sort Add sort keys to output
 * @return array List of fields
 */
	protected function _scaffoldFields($sort = true) {
		$model = $this->_model();
		$request = $this->_request();
		$modelSchema = $model->schema();

		$_fields = array();
		$scaffoldFields = array_keys($modelSchema);
		foreach ($scaffoldFields as $scaffoldField) {
			$_fields[$scaffoldField] = array();
		}
		$scaffoldFields = $_fields;

		$_scaffoldFields = $this->_action()->config('scaffoldFields');
		if (!empty($_scaffoldFields)) {
			$_fields = array();
			$_scaffoldFields = (array)$_scaffoldFields;
			foreach ($_scaffoldFields as $name => $options) {
				if (is_numeric($name) && !is_array($options)) {
					$name = $options;
					$options = array();
				}
				$_fields[$name] = $options;
			}

			$scaffoldFields = array_intersect_key($scaffoldFields, $_fields);
		}

		$singularTable = Inflector::singularize($model->table);

		if ($sort) {
			foreach ($scaffoldFields as $_field => $_options) {
				$entity = explode('.', $_field);
				$scaffoldFields[$_field]['__field__'] = $_field;
				$scaffoldFields[$_field]['__display_field__'] = false;
				$scaffoldFields[$_field]['__schema__'] = null;
				if (count($entity) == 1 || current($entity) == $model->alias) {
					$scaffoldFields[$_field]['__display_field__'] = in_array(end($entity), array(
						$model->displayField,
						$singularTable,
					));
					$scaffoldFields[$_field]['__schema__'] = $modelSchema[end($entity)]['type'];
				}
			}
		}

		return $scaffoldFields;
	}

/**
 * Returns fields to be allowed for display on scaffolded view
 *
 * @param array $scaffoldFields
 * @param boolean $sort Sort fields
 * @return array List of fields
 */
	protected function _scaffoldFieldExclude($scaffoldFields, $sort = true) {
		$model = $this->_model();
		$modelSchema = $model->schema();
		$className = $this->_action()->config('className');
		$blacklist = $this->_action()->config('scaffoldFieldExclude');

		if (empty($blacklist)) {
			if ($className == 'Crud.Add' || $className == 'Crud.Edit') {
				$blacklist = array('created', 'modified', 'updated');
				foreach ($scaffoldFields as $_field => $_options) {
					if (substr($_field, -6) === '_count') {
						$blacklist[] = $_field;
					}
				}
			} else {
				$blacklist = array();
			}
		}

		$scaffoldFields = array_diff_key($scaffoldFields, array_combine(
			$blacklist, $blacklist
		));

		if ($sort) {
			uasort($scaffoldFields, array('ScaffoldListener', '_compareFields'));
			$scaffoldFields = array_reverse($scaffoldFields, true);
			foreach ($scaffoldFields as $_field => $_options) {
				unset(
					$scaffoldFields[$_field]['__field__'],
					$scaffoldFields[$_field]['__display_field__'],
					$scaffoldFields[$_field]['__schema__']
				);
			}
		}

		return $scaffoldFields;
	}

	protected static function _compareFields($one, $two) {
		$_primary = 10;
		$_displayField = 9;
		$_select = 8;
		$_other = 5;
		$_boolean = 2;
		$_count = 1;
		$_date = 0;

		$a = $_other;
		$b = $_other;

		if ($one['__field__'] == 'id') {
			$a = $_primary;
		} elseif ($one['__display_field__']) {
			$a = $_displayField;
		} elseif (substr($one['__field__'], -3) === '_id') {
			$a = $_select;
		} elseif ($one['__schema__'] == 'boolean') {
			$a = $_boolean;
		} elseif (substr($one['__field__'], -6) === '_count') {
			$a = $_count;
		} elseif (in_array($one['__schema__'], array('date', 'datetime', 'timestamp', 'time'))) {
			$a = $_date;
		}

		if ($two['__field__'] == 'id') {
			$b = $_primary;
		} elseif ($two['__display_field__']) {
			$b = $_displayField;
		} elseif (substr($two['__field__'], -3) === '_id') {
			$b = $_select;
		} elseif ($two['__schema__'] == 'boolean') {
			$b = $_boolean;
		} elseif (substr($two['__field__'], -6) === '_count') {
			$b = $_count;
		} elseif (in_array($two['__schema__'], array('date', 'datetime', 'timestamp', 'time'))) {
			$b = $_date;
		}

		if ($a == $b) {
			$r = array($one['__field__'], $two['__field__']);
			sort($r);
			return ($r[0] == $one['__field__']) ? 1 : -1;
		}
		return ($a < $b) ? -1 : 1;
	}

/**
 * Returns fields to be filtered upon in scaffolded view
 *
 * @param CakeRequest $request
 * @return array Array of fields to show filters for
 */
	protected function _scaffoldFilters(CakeRequest $request) {
		$scaffoldFilters = array();
		$_scaffoldFilters = $this->_action()->config('scope');
		if (!empty($_scaffoldFilters)) {
			$scaffoldFilters = (array)$_scaffoldFilters;
			foreach ($scaffoldFilters as $_field => $scaffoldField) {
				$scaffoldFilters[$_field] = Hash::merge(array(
					'type' => 'value',
					'form' => array(
						'label' => false,
						'placeholder' => $_field,
					),
				), $scaffoldField);
				$scaffoldFilters[$_field] = $scaffoldFilters[$_field]['form'];

				if (!isset($scaffoldFilters[$_field]['value'])) {
					$scaffoldFilters[$_field]['value'] = $request->query($_field);
				}
			}
		}

		return $scaffoldFilters;
	}


/**
 * Returns links to be shown in actions section of scaffolded view
 *
 * @return array Array of link
 */
	protected function _sidebarActions() {
		$sidebarActions = $this->_action()->config('sidebarActions');
		if ($sidebarActions === null) {
			return true;
		}

		foreach ($sidebarActions as $i => $sidebarAction) {
			$sidebarActions[$i] = array_merge(array(
				'title' => null,
				'url' => null,
				'options' => array(),
				'confirmMessage' => false,
				'type' => 'link',
			), $sidebarActions[$i]);

			$sidebarActions[$i]['type'] = strtolower($sidebarActions[$i]['type']);
			if (!in_array($sidebarActions[$i]['type'], array('link', 'post'))) {
				$sidebarActions[$i]['type'] = 'link';
			}
		}

		return $sidebarActions;
	}

/**
 * Returns associations for controllers models.
 *
 * @param Model $model
 * @return array Associations for model
 */
	protected function _associations(Model $model) {
		$associations = array();

		$associated = $model->getAssociated();
		foreach ($associated as $assocKey => $type) {
			if (!isset($associations[$type])) {
				$associations[$type] = array();
			}

			$assocDataAll = $model->$type;

			$assocData = $assocDataAll[$assocKey];
			$associatedModel = $model->{$assocKey};

			$associations[$type][$assocKey]['primaryKey'] = $associatedModel->primaryKey;
			$associations[$type][$assocKey]['displayField'] = $associatedModel->displayField;
			$associations[$type][$assocKey]['foreignKey'] = $assocData['foreignKey'];

			list($plugin, $modelClass) = pluginSplit($assocData['className']);

			if ($plugin) {
				$plugin = Inflector::underscore($plugin);
			}

			$associations[$type][$assocKey]['plugin'] = $plugin;
			$associations[$type][$assocKey]['controller'] = Inflector::pluralize(Inflector::underscore($modelClass));

			if ($type === 'hasAndBelongsToMany') {
				$associations[$type][$assocKey]['with'] = $assocData['with'];
			}
		}

		return $associations;
	}

}
