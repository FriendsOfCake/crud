<?php
namespace Crud\Action;

use Cake\Utility\Inflector;
use Crud\Event\Subject;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\ViewTrait;
use Crud\Traits\ViewVarTrait;

/**
 * Handles 'View' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class View extends Base {

	use FindMethodTrait;
	use ViewTrait;
	use ViewVarTrait;

/**
 * Default settings for 'view' actions
 *
 * `enabled` Is this crud action enabled or disabled
 *
 * `findMethod` The default `Model::find()` method for reading data
 *
 * `view` A map of the controller action and the view to render
 * If `NULL` (the default) the controller action name will be used
 *
 * @var array
 */
	protected $_settings = [
		'enabled' => true,
		'scope' => 'entity',
		'findMethod' => 'all',
		'view' => null,
		'viewVar' => null,
		'serialize' => []
	];

/**
 * HTTP GET handler
 *
 * @param string $id
 * @return void
 */
	protected function _get($id = null) {
		$subject = $this->_subject();
		$subject->set(['id' => $id, 'viewVar' => $this->viewVar()]);

		$this->_findRecord($id, $subject);
		$this->_controller()->set(['success' => $subject->success, $subject->viewVar => $subject->item]);
		$this->_trigger('beforeRender', $subject);
	}

}
