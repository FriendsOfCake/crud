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
		$model = $this->_model();
		$request = $this->_request();
		$controller = $this->_controller();
		$action = $request->action;

		$scaffoldTitle = Inflector::humanize(Inflector::underscore($controller->viewPath));
		$titleForLayout = Inflector::humanize($request->action) . ' :: ' . $scaffoldTitle;

		$modelClass = $controller->modelClass;
		$primaryKey = $model->primaryKey;
		$displayField = $model->displayField;
		$singularVar = Inflector::variable($modelClass);
		$pluralVar = Inflector::variable($controller->name);
		$singularHumanName = Inflector::humanize(Inflector::underscore($modelClass));
		$pluralHumanName = Inflector::humanize(Inflector::underscore($controller->name));
		$modelSchema = $model->schema();
		$associations = $this->_associations($model);

		$scaffoldPrimaryKeyValue = $this->_scaffoldPrimaryKeyValue();
		$scaffoldDisplayFieldValue = $this->_scaffoldDisplayFieldValue();
		$scaffoldAdminTitle = $this->_scaffoldAdminTitle();
		$scaffoldPageTitle = $this->_scaffoldPageTitle($singularHumanName, $scaffoldPrimaryKeyValue, $scaffoldDisplayFieldValue);

		$scaffoldFilters = $this->_scaffoldFilters($request);
		$scaffoldNavigation = $this->_scaffoldNavigation($request);
		$scaffoldControllerActions = $this->_scaffoldControllerActions();
		$scaffoldSidebarActions = $this->_scaffoldSidebarActions(
			$pluralHumanName,
			$singularHumanName,
			$associations,
			$scaffoldPrimaryKeyValue
		);

		$_sort = $this->_action($request->action)->config('scaffoldFields');
		$_sort = empty($_sort);
		$scaffoldFieldsData = $this->_scaffoldFields($model, $request, $_sort);
		$scaffoldFieldsData = $this->_scaffoldFieldExclude($model, $request, $scaffoldFieldsData, $_sort);
		$scaffoldFields = array_keys($scaffoldFieldsData);

		$redirectUrl = $this->_refererRedirectUrl(array('action' => 'index'));
		$request->data['redirect_url'] = $redirectUrl;

		$controller->set(compact(
			'modelClass', 'primaryKey', 'displayField', 'singularVar', 'pluralVar',
			'singularHumanName', 'pluralHumanName', 'scaffoldFields', 'associations',
			'scaffoldFilters', 'action', 'modelSchema', 'scaffoldSidebarActions',
			'scaffoldNavigation', 'scaffoldControllerActions', 'scaffoldFieldsData',
			'scaffoldPrimaryKeyValue', 'scaffoldDisplayFieldValue',
			'scaffoldAdminTitle', 'scaffoldPageTitle'
		));
		$controller->set(array(
			'redirect_url' => $redirectUrl
		));

		$controller->set('title_for_layout', $titleForLayout);

		if ($controller->viewClass) {
			$controller->viewClass = 'Scaffold';
		}

		$controller->helpers = (array)$controller->helpers;
		$controller->helpers[] = 'Time';
		$controller->helpers[] = 'Crud.Crud';

		$controller->layout = 'Crud.default';

		App::build(array(
			'View' => array(
				APP . 'View' . DS,
				CakePlugin::path('Crud') . 'View' . DS
			)
		));
	}

/**
 * Returns the admin title to show on scaffolded view
 *
 * @return string
 */
	protected function _scaffoldAdminTitle() {
		$request = $this->_request();
		$scaffoldTitle = $this->_action($request->action)->config('scaffoldAdminTitle');
		if (empty($scaffoldTitle)) {
			$scaffoldTitle = 'Admin';
		}

		return $scaffoldTitle;
	}

/**
 * Returns the admin title to show on scaffolded view
 *
 * @return string
 */
	protected function _scaffoldPageTitle($singularHumanName, $primaryKeyValue = null, $displayFieldValue = null) {
		$request = $this->_request();
		$scaffoldTitle = $this->_action($request->action)->config('scaffoldPageTitle');
		if (empty($scaffoldTitle)) {
			$actionName = Inflector::humanize(Inflector::underscore($request->action));
			$humanName = $this->_controllerName();

			if ($primaryKeyValue === null && $displayFieldValue === null) {
				$scaffoldTitle = sprintf('%s %s', $actionName, $humanName);
			} elseif ($displayFieldValue === null) {
				$scaffoldTitle = sprintf('%s %s #%s', $actionName, $humanName, $primaryKeyValue);
			} elseif ($primaryKeyValue === null) {
				$scaffoldTitle = sprintf('%s %s %s', $actionName, $humanName, $displayFieldValue);
			} else {
				$scaffoldTitle =sprintf('%s %s #%s: %s', $actionName, $humanName, $primaryKeyValue, $displayFieldValue);
			}
		}
		return $scaffoldTitle;
	}

	protected function _controllerName() {
		$controller = $this->_controller();
		$request = $this->_request();
		$action = $this->_action($request->action);

		$type = $action::ACTION_SCOPE;
		if ($type === CrudAction::SCOPE_MODEL) {
			return Inflector::pluralize(Inflector::humanize(Inflector::underscore($controller->viewPath)));
		} elseif ($type === CrudAction::SCOPE_RECORD) {
			return Inflector::singularize(Inflector::humanize(Inflector::underscore($controller->viewPath)));
		}
	}

/**
 * Returns fields to be displayed on scaffolded view
 *
 * @param Model $model
 * @param CakeRequest $request
 * @param boolean $sort Add sort keys to output
 * @return array List of fields
 */
	protected function _scaffoldFields(Model $model, CakeRequest $request, $sort = true) {
		$modelSchema = $model->schema();

		$_fields = array();
		$scaffoldFields = array_keys($modelSchema);
		foreach ($scaffoldFields as $scaffoldField) {
			$_fields[$scaffoldField] = array();
		}
		$scaffoldFields = $_fields;

		$_scaffoldFields = $this->_action($request->action)->config('scaffoldFields');
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
 * @param Model $model
 * @param CakeRequest $request
 * @param array $scaffoldFields
 * @param boolean $sort Sort fields
 * @return array List of fields
 */
	protected function _scaffoldFieldExclude(Model $model, CakeRequest $request, $scaffoldFields, $sort = true) {
		$modelSchema = $model->schema();
		$className = $this->_action($request->action)->config('className');
		$blacklist = $this->_action($request->action)->config('scaffoldFieldExclude');

		if (empty($blacklist)) {
			$blacklist = array();
			if ($className == 'Crud.Add' || $className == 'Crud.Edit') {
				$blacklist = array('created', 'modified', 'updated');
				foreach ($scaffoldFields as $_field => $_options) {
					if (substr($_field, -6) === '_count') {
						$blacklist[] = $_field;
					}
				}
			}
		}

		if (!empty($blacklist)) {
			$scaffoldFields = array_diff_key($scaffoldFields, array_combine(
				$blacklist, $blacklist
			));
		}

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

/**
 * Comparison method for sorting view fields
 *
 * @return integer Result of comparison
 */
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
		$_scaffoldFilters = $this->_action($request->action)->config('scope');
		if (!empty($_scaffoldFilters)) {
			$scaffoldFilters = (array)$_scaffoldFilters;
			foreach ($scaffoldFilters as $_field => $scaffoldField) {
				$scaffoldFilters[$_field] = Hash::merge(array(
					'type' => 'value',
					'form' => array(),
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
 * @return mixed Array of initialized links, or boolean as to whether or not to show actions
 */
	protected function _scaffoldSidebarActions($pluralHumanName, $singularHumanName, $associations, $primaryKeyValue = null) {
		$request = $this->_request();
		$scaffoldSidebarActions = $this->_action($request->action)->config('sidebarActions');
		if ($scaffoldSidebarActions === false) {
			return false;
		}

		if ($scaffoldSidebarActions === null) {
			$scaffoldSidebarActions = array(
				array('_type' => 'header', 'title' => __d('crud', 'Actions'))
			);
			$controllerActions = $this->_scaffoldControllerActions();
			foreach ($controllerActions['model'] as $_action) {
				if ($request->action != $_action) {
					$scaffoldSidebarActions[] = array(
						'title' => sprintf('%s %s', Inflector::humanize($_action), $pluralHumanName),
						'url' => array('action' => $_action),
					);
				}
			}

			if (!in_array($request->action, $controllerActions['model'])) {
				foreach ($controllerActions['record'] as $_action) {
					if ($request->action != $_action) {
						$scaffoldSidebarActions[] = array(
							'title' => sprintf('%s %s', Inflector::humanize($_action), $singularHumanName),
							'url' => array('action' => $_action, $primaryKeyValue)
						);
					}
				}
			}
		}

		if ($this->_scaffoldRelatedActions()) {
			$scaffoldSidebarActions[] = array('_type' => 'header', 'title' => __d('crud', 'Related Actions'));
			$done = array();
			foreach ($associations as $_type => $_data) {
				foreach ($_data as $_alias => $_details) {
					if ($_details['controller'] != $request->controller && !in_array($_details['controller'], $done)) {
						$scaffoldSidebarActions[] = array(
							'title' => __d('crud', 'List %s', Inflector::humanize($_details['controller'])),
							'url' => array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'index'),
						);

						$scaffoldSidebarActions[] = array(
							'title' => __d('crud', 'New %s', Inflector::humanize(Inflector::underscore($_alias))),
							'url' => array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'add'),
						);
						$done[] = $_details['controller'];
					}
				}
			}
		}

		foreach ($scaffoldSidebarActions as $i => $_item) {
			$scaffoldSidebarActions[$i] = $this->_makeLink($_item);
		}

		return $scaffoldSidebarActions;
	}

/**
 * Returns groupings of action types on the scaffolded view
 *
 * @return string
 */
	protected function _scaffoldControllerActions() {
		$_actions = $this->_crud()->config('actions');

		$model = array();
		$record = array();
		foreach ($_actions as $_actionName => $_config) {
			$_action = $this->_action($_actionName);
			$_type = $_action::ACTION_SCOPE;
			if ($_type === CrudAction::SCOPE_MODEL) {
				$model[] = $_actionName;
			} elseif ($_type === CrudAction::SCOPE_RECORD) {
				$record[] = $_actionName;
			}
		}

		return compact('model', 'record');
	}

/**
 * Returns whether or not related items should displayed on scaffolded view
 *
 * @return boolean
 */
	protected function _scaffoldRelatedActions() {
		$request = $this->_request();
		$scaffoldRelatedActions = $this->_action($request->action)->config('scaffoldRelatedActions');
		if ($scaffoldRelatedActions === null) {
			$scaffoldRelatedActions = true;
		} else {
			$scaffoldRelatedActions = (bool)$scaffoldRelatedActions;
		}
		return $scaffoldRelatedActions;
	}

/**
 * Returns links to be shown in navigation section of scaffolded view
 *
 * @param CakeRequest $request
 * @return mixed Array of initialized links, or false for no navigation
 */
	protected function _scaffoldNavigation(CakeRequest $request) {
		$request = $this->_request();
		$scaffoldNavigation = $this->_action($request->action)->config('scaffoldNavigation');
		if (!is_array($scaffoldNavigation)) {
			return false;
		}

		foreach ($scaffoldNavigation as $i => $_item) {
			$scaffoldNavigation[$i] = $this->_makeLink($_item);
		}

		return $scaffoldNavigation;
	}

	protected function _scaffoldPrimaryKeyValue() {
		$controller = $this->_controller();
		$model = $this->_model();
		$primaryKeyValue = null;
		$path = null;

		if (!empty($controller->modelClass) && !empty($model->primaryKey)) {
			$path = "{$controller->modelClass}.{$model->primaryKey}";
			if (!empty($controller->data)) {
				$primaryKeyValue = Hash::get($controller->data, $path);
			}

			$singularVar = Inflector::variable($controller->modelClass);
			if (!empty($controller->viewVars[$singularVar])) {
				$primaryKeyValue = Hash::get($controller->viewVars[$singularVar], $path);
			}
		}

		return $primaryKeyValue;
	}

	protected function _scaffoldDisplayFieldValue() {
		$controller = $this->_controller();
		$model = $this->_model();
		$displayFieldValue = null;
		$path = null;

		if (!empty($controller->modelClass) && !empty($model->displayField) && $model->displayField != $model->primaryKey) {
			$path = "{$controller->modelClass}.{$model->displayField}";
			if (!empty($controller->data)) {
				$displayFieldValue = Hash::get($controller->data, $path);
			}

			$singularVar = Inflector::variable($controller->modelClass);
			if (!empty($controller->viewVars[$singularVar])) {
				$displayFieldValue = Hash::get($controller->viewVars[$singularVar], $path);
			}
		}

		return $displayFieldValue;
	}

/**
 * Initializes all data necessary for Html::link() and Form::postLink() calls
 *
 * @return array Link data
 */
	protected function _makeLink($data) {
		$data = array_merge(array(
				'title' => null,
				'url' => null,
				'options' => array(),
				'confirmMessage' => false,
				'_type' => 'link'
		), $data);

		if (!in_array($data['_type'], array('link', 'header'))) {
			$data['_type'] = 'link';
		}

		return $data;
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

		if (empty($associations['hasMany'])) {
			$associations['hasMany'] = array();
		}
		if (empty($associations['hasAndBelongsToMany'])) {
			$associations['hasAndBelongsToMany'] = array();
		}

		return $associations;
	}

}
