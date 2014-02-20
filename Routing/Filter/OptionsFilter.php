<?php

App::uses('DispatcherFilter', 'Routing');

/**
 * OptionsFilter
 *
 * Automatically handle options requests
 */
class OptionsFilter extends DispatcherFilter {

/**
 * priority
 *
 * Run last, allowing for another filter to add more headers to the response if necessary
 * by running with a lower priority
 *
 * @var int
 */
	public $priority = 11;

/**
 * defaultVerbs
 *
 * The list of verbs to check, if not overwritten by calling:
 *
 * 	Configure::write('Crud.OptionsFilter.verbs', ['list', 'of', 'verbs']);
 *
 * @var array
 */
	public $defaultVerbs = array(
		'GET',
		'POST',
		'PUT',
		'DELETE',
	);

/**
 * Handle Options requests
 *
 * If it's an options request, loop on the configured http verbs and add
 * an Access-Control-Allow-Methods header with the verbs the application
 * is configured to respond to.
 *
 * @param CakeEvent $event
 * @return CakeResponse|null
 */
	public function beforeDispatch(CakeEvent $event) {
		$request = $event->data['request'];

		if (!$request->is('options')) {
			return;
		}

		$event->stopPropagation();

		$url = $request->url;
		$verbs = Configure::read('Crud.OptionsFilter.verbs') ?: $this->defaultVerbs;
		$allowedMethods = array();

		foreach ($verbs as $verb) {
			$_SERVER['REQUEST_METHOD'] = $verb;
			if (Router::parse('/' . $url)) {
				$allowedMethods[] = $verb;
			}
		}
		$_SERVER['REQUEST_METHOD'] = 'OPTIONS';

		$response = $event->data['response'];
		$response->header('Access-Control-Allow-Methods', implode(', ', $allowedMethods));
		return $response;
	}
}
