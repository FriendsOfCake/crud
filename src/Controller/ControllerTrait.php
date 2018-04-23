<?php
namespace Crud\Controller;

use Cake\Controller\Exception\MissingActionException;

/**
 * Enable Crud to catch MissingActionException and attempt to generate response
 * using Crud.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
trait ControllerTrait
{

    /**
     * List of components that are capable of dispatching an action that is
     * not already implemented
     *
     * @var array
     */
    public $dispatchComponents = [];

    /**
     * Get controller name.
     *
     * Added for backwards compatibility with CakePHP 3.5.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Dispatches the controller action. Checks that the action exists and isn't private.
     *
     * If CakePHP raises MissingActionException we attempt to execute Crud
     *
     * @return mixed The resulting response.
     * @throws \LogicException When request is not set.
     * @throws \Cake\Controller\Exception\MissingActionException When actions are not defined
     *   and CRUD is not enabled.
     */
    public function invokeAction()
    {
        $request = $this->request;
        if (!isset($request)) {
            throw new \LogicException('No Request object configured. Cannot invoke action');
        }
        if (!$this->isAction($request->getParam('action'))) {
            throw new MissingActionException([
                'controller' => $this->name . 'Controller',
                'action' => $request->getParam('action'),
                'prefix' => $request->getParam('prefix') ?: '',
                'plugin' => $request->getParam('plugin'),
            ]);
        }

        $callable = [$this, $request->getParam('action')];
        if (is_callable($callable)) {
            return call_user_func_array($callable, $request->getParam('pass'));
        }

        $component = $this->_isActionMapped();
        if ($component) {
            return $component->execute();
        }

        throw new MissingActionException([
            'controller' => $this->name . 'Controller',
            'action' => $request->getParam('action'),
            'prefix' => $request->getParam('prefix') ?: '',
            'plugin' => $request->getParam('plugin'),
        ]);
    }

    /**
     * Return true for a mapped action so that AuthComponent doesn't skip
     * authentication / authorization for that action.
     *
     * @param string $action Action name
     * @return bool True is action is mapped and enabled.
     */
    public function isAction($action)
    {
        $isAction = parent::isAction($action);
        if ($isAction) {
            return true;
        }

        if ($this->_isActionMapped()) {
            return true;
        }

        return false;
    }

    /**
     * Check if an action can be dispatched using CRUD.
     *
     * @return bool|\Cake\Controller\Component The component instance if action is
     *  mapped else `false`.
     */
    protected function _isActionMapped()
    {
        if (!empty($this->dispatchComponents)) {
            foreach ($this->dispatchComponents as $component => $enabled) {
                if (empty($enabled)) {
                    continue;
                }

                // Skip if isActionMapped isn't defined in the Component
                if (!method_exists($this->{$component}, 'isActionMapped')) {
                    continue;
                }

                // Skip if the action isn't mapped
                if (!$this->{$component}->isActionMapped()) {
                    continue;
                }

                // Skip if execute isn't defined in the Component
                if (!method_exists($this->{$component}, 'execute')) {
                    continue;
                }

                // Return the component instance.
                return $this->{$component};
            }
        }

        return false;
    }
}
