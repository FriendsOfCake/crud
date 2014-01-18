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
 * Generic handler
 *
 * @return void
 */
	protected function _handle() {
		$Subject = $this->_subject(['success' => true, 'viewVar' => $this->viewVar()]);

		$Event = $this->_trigger('beforePaginate', $Subject);
		$items = $this->_controller()->paginate();
		$Subject->set(['items' => $items]);
		$Event = $this->_trigger('afterPaginate', $Subject);

		$this->_controller()->set(['success' => $Subject->success, $Subject->viewVar => $items]);
		$this->_trigger('beforeRender', $Subject);
	}

}
