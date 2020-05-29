<?php
declare(strict_types=1);

namespace Crud\Test\App\Controller\Component;

use Crud\Controller\Component\CrudComponent;

/**
 * TestCrudComponent
 *
 * Expose protected methods so we can test them in isolation
 */
class TestCrudComponent extends CrudComponent
{
    /**
     * test visibility wrapper - access protected _modelName property
     */
    public function getModelName()
    {
        return $this->_modelName;
    }

    /**
     * test visibility wrapper - allow on the fly change of action name
     */
    public function setAction($name)
    {
        $this->_action = $name;
    }
}
