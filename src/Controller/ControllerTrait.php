<?php
declare(strict_types=1);

namespace Crud\Controller;

use Cake\Controller\Component;
use Cake\Controller\Exception\MissingActionException;
use Psr\Http\Message\ResponseInterface;

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
     * Dispatches the controller action. Checks that the action exists and isn't private.
     *
     * If a controller method with required name does not exist we attempt to execute Crud action.
     *
     * @return \Psr\Http\Message\ResponseInterface|null The resulting response.
     * @throws \Cake\Controller\Exception\MissingActionException When required
     *   controller method or mapped Crud action does not exist or disabled.
     */
    public function invokeAction(): ?ResponseInterface
    {
        $request = $this->request;

        $result = null;
        if ($this->isAction($request->getParam('action'))) {
            $callable = [$this, $request->getParam('action')];
            $result = $callable(...array_values($request->getParam('pass')));
        } else {
            $component = $this->_isActionMapped();
            if ($component) {
                $result = $component->execute();
            } else {
                throw new MissingActionException([
                    'controller' => $this->name . 'Controller',
                    'action' => $request->getParam('action'),
                    'prefix' => $request->getParam('prefix') ?: '',
                    'plugin' => $request->getParam('plugin'),
                ]);
            }
        }

        if ($result === null) {
            return $result;
        }

        return $this->response = $result;
    }

    /**
     * Check if an action can be dispatched using CRUD.
     *
     * @return \Cake\Controller\Component|null The component instance if action is
     *  mapped else `null`.
     */
    protected function _isActionMapped(): Component
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

        return null;
    }
}
