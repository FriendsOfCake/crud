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
     * test visibility wrapper - access protected modelName property
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * test visibility wrapper - allow on the fly change of action name
     */
    public function setAction($name)
    {
        $this->action = $name;
    }
}
