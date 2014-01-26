<?php
namespace Crud\Action;

use Cake\Utility\Inflector;
use Crud\Traits\ViewTrait;
use Crud\Traits\ViewVarTrait;

/**
 * Handles 'Index' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class Index extends Base {

	use ViewTrait;
	use ViewVarTrait;

/**
 * Default settings for 'index' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `view` A map of the controller action and the view to render
 * If `NULL` (the default) the controller action name will be used
 *
 * @var array
 */
	protected $_settings = [
		'enabled' => true,
		'scope' => 'repository',
		'view' => null,
		'viewVar' => null,
		'serialize' => [],
		'api' => [
			'success' => [
				'code' => 200
			],
			'error' => [
				'code' => 400
			]
		]
	];

/**
 * Generic handler for all HTTP verbs
 *
 * @return void
 */
	protected function _handle() {
		$subject = $this->_subject();
		$subject->set(['success' => true, 'viewVar' => $this->viewVar()]);

		$this->_trigger('beforePaginate', $subject);

		$controller = $this->_controller();
		$items = $controller->paginate();
		$subject->set(['items' => $items]);

		$this->_trigger('afterPaginate', $subject);

		$controller->set(['success' => $subject->success, $subject->viewVar => $subject->items]);
		$this->_trigger('beforeRender', $subject);
	}

}
