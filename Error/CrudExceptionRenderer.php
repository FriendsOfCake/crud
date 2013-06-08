<?php

App::uses('ExceptionRenderer', 'Error');

class CrudExceptionRenderer extends ExceptionRenderer {

/**
 * Generate the response using the controller object.
 *
 * If there is no specific template for the raised error (normally there won't be one)
 * swallow the missing view exception and just use the standard
 * error format. This prevents throwing an unknown Exception and seeing instead
 * a MissingView exception
 *
 * @param string $template The template to render.
 * @return void
 */
	protected function _outputMessage($template) {
		try {
			$this->controller->set('success', false);
			$this->controller->set('data', $this->_getErrorData());
			$this->controller->set('_serialize', array('success', 'data'));
			$this->controller->render($template);
			$this->controller->afterFilter();
			$this->controller->response->send();
		} catch (MissingViewException $e) {
			$this->_outputMessageSafe('error500');
		} catch (Exception $e) {
			$this->controller->set(array(
				'error' => $e,
				'name' => $e->getMessage(),
				'code' => $e->getCode()
			));
			$this->_outputMessageSafe('error500');
		}
	}

/**
 * A safer way to render error messages, replaces all helpers, with basics
 * and doesn't call component methods.
 *
 * @param string $template The template to render
 * @return void
 */
	protected function _outputMessageSafe($template) {
		$this->controller->layoutPath = '';
		$this->controller->subDir = '';
		$this->controller->viewPath = 'Errors/';
		$this->controller->viewClass = 'View';
		$this->controller->helpers = array('Form', 'Html', 'Session');

		$this->controller->render($template);
		$this->controller->response->send();
	}

/**
 * Helper method used to generate  extra debugging data into the error template
 *
 * @return array debugging data
 */
	protected function _getErrorData() {
		$data = array();

		$viewVars = $this->controller->viewVars;
		foreach ($viewVars['_serialize'] as $v) {
			$data[$v] = $viewVars[$v];
		}

		if (!empty($viewVars['error'])) {
			$data['exception'] = array(
				'class' => get_class($viewVars['error']),
				'code' => $viewVars['error']->getCode(),
				'message' => $viewVars['error']->getMessage()
			);
		}

		if (Configure::read('debug')) {
			$data['exception']['trace'] = preg_split('@\n@', $viewVars['error']->getTraceAsString());

			$previous = $viewVars['error']->getPrevious();
			if ($previous) {
				$data['exception']['previous'] = array(
					'class' => get_class($previous),
					'code' => $previous->getCode(),
					'message' => $previous->getMessage(),
					'trace' => preg_split('@\n@', $previous->getTraceAsString())
				);
			}

		}
		if (class_exists('ConnectionManager') && Configure::read('debug') > 1) {
			$sources = ConnectionManager::sourceList();
			$queryLog = array();
			foreach ($sources as $source) {
				$db = ConnectionManager::getDataSource($source);
				if (!method_exists($db, 'getLog')) {
					continue;
				}
				$data['queryLog'][$source] = $db->getLog(false, false);
			}
		}

		return $data;
	}
}
