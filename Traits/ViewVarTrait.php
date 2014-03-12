<?php
namespace Crud\Traits;

use Cake\Utility\Inflector;

trait ViewVarTrait {

/**
 * Change the name of the view variable name
 * of the data when its sent to the view
 *
 * @param mixed $name
 * @return mixed
 */
	public function viewVar($name = null) {
		if (empty($name)) {
			return $this->config('viewVar') ?: Inflector::variable($this->_controller()->name);
		}

		return $this->config('viewVar', $name);
	}

}
