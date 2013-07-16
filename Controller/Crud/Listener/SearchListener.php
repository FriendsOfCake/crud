<?php

App::uses('CrudListener', 'Crud.Controller/Crud');

/**
 * Search Listener
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class SearchListener extends CrudListener {

/**
 * Default configuration
 *
 * @var array
 */
	protected $_settings = array(
		'component' => array(
			'commonProcess' => array(
				'paramType' => 'querystring'
			),
			'presetForm' => array(
				'paramType' => 'querystring'
			)
		),
		'scope' => array()
	);

/**
 * Returns a list of all events that will fire in the controller during it's lifecycle.
 * You can override this function to add you own listener callbacks
 *
 * We attach at priority 50 so normal bound events can run before us
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.beforePaginate' => array('callable' => 'beforePaginate', 'priority' => 50)
		);
	}

/**
 * Define a new scope
 *
 * @param string $name Name of the scope (?scope=$name)
 * @param array $query The query arguments to pass to Search
 * @param array|null $filter The filterArgs to use on the model
 * @return ScopedListener
 */
	public function scope($name, $query, $filter = null) {
		$this->config('scope.' . $name, compact('query', 'filter'));
		return $this;
	}

/**
 * beforePaginate callback
 *
 * @param CakeEvent $e
 * @return void
 */
	public function beforePaginate(CakeEvent $e) {
		$this->_checkRequiredPlugin();
		$this->_ensureComponent($this->_controller);
		$this->_ensureBehavior($this->_model);
		$this->_commonProcess($this->_controller, $this->_model->name);

		$query = $this->_request->query;
		if (!empty($this->_request->query['_scope'])) {
			$config = $this->config('scope.' . $this->_request->query['_scope']);
			$query = $config['query'];

			if (!empty($config['filter'])) {
				$this->_setFilterArgs($this->_model, $config['filter']);
			}
		}

		// Avoid notice if there is no filterArgs
		if (empty($this->_model->filterArgs)) {
			$this->_setFilterArgs($this->_model, array());
		}

		$this->_setPaginationOptions($this->_controller, $this->_model, $query);
	}

/**
 * Check that the cakedc/search plugin is installed
 *
 * @throws CakeException If cakedc/search isn't loaded
 * @return void
 */
	protected function _checkRequiredPlugin() {
		if (CakePlugin::loaded('Search')) {
			return;
		}

		throw new CakeException('SearchListener requires the CakeDC/search plugin. Please install it from https://github.com/CakeDC/search');
	}

/**
 * Ensure that the Prg component is loaded from
 * the Search plugin
 *
 * @param Controller $controller
 * @return void
 */
	protected function _ensureComponent(Controller $controller) {
		if ($controller->Components->loaded('Prg')) {
			return;
		}

		$controller->Prg = $controller->Components->load('Search.Prg', $this->config('component'));
		$controller->Prg->initialize($controller);
		$controller->Prg->startup($controller);
	}

/**
 * Ensure that the searchable behavior is loaded
 *
 * @param Model $model
 * @return void
 */
	protected function _ensureBehavior(Model $model) {
		if ($model->Behaviors->loaded('Searchable')) {
			return;
		}

		$model->Behaviors->load('Search.Searchable');
		$model->Behaviors->Searchable->setup($model);
	}

/**
 * Execute commonProcess on Prg component
 *
 * @codeCoverageIgnore
 * @param Controller $controller
 * @param string $modelClass
 * @return void
 */
	protected function _commonProcess(Controller $controller, $modelClass) {
		$controller->Prg->commonProcess($modelClass);
	}

/**
 * Set the pagination options
 *
 * @codeCoverageIgnore
 * @param Controller $controller
 * @param Model $model
 * @param array $query
 * @return void
 */
	protected function _setPaginationOptions(Controller $controller, Model $model, $query) {
		$controller->Paginator->settings['conditions'] = $model->parseCriteria($query);
	}

/**
 * Set the model filter args
 *
 * @codeCoverageIgnore
 * @param Model $model
 * @param array $filter
 * @return void
 */
	protected function _setFilterArgs(Model $model, $filter) {
		$model->filterArgs = $filter;
		$model->Behaviors->Searchable->setup($model);
	}

}
