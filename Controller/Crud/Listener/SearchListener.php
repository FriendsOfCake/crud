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
		'scope' => array(

		)
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
		$controller = $this->_controller;
		$request = $this->_request;
		$model = $e->subject->model;

		$this->_checkRequiredPlugin();
		$this->_ensureComponent($controller);
		$this->_ensureBehavior($model);

		$controller->Prg->commonProcess($e->subject->modelClass);

		$query = $controller->query;
		if (!empty($request->query['_scope'])) {
			$config = $this->config('scope.' . $request->query['_scope']);
			$query = $config['query'];

			if (!empty($config['filter'])) {
				$model->filterArgs = $config['filter'];
				$model->Behaviors->Searchable->setup($model);
			}
		}

		// Avoid notice if there is no filterArgs
		if (empty($model->filterArgs)) {
			$model->filterArgs = array();
		}

		$controller->Paginator->settings['conditions'] = $model->parseCriteria($query);
	}

/**
 * Check that the cakedc/search plugin is installed
 * and loaded
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
 * @param Controller $Controller
 * @return void
 */
	protected function _ensureComponent(Controller $Controller) {
		if ($Controller->Components->loaded('Prg')) {
			return;
		}

		$Controller->Prg = $Controller->Components->load('Search.Prg', $this->config('component'));
		$Controller->Prg->startup($Controller);
		$Controller->Prg->initialize($Controller);
	}

/**
 * Ensure that the searchable behavior is loaded
 *
 * @param Model $Model
 * @return void
 */
	protected function _ensureBehavior(Model $Model) {
		if ($Model->Behaviors->loaded('Searchable')) {
			return;
		}

		$Model->Behaviors->load('Search.Searchable');
	}

}
