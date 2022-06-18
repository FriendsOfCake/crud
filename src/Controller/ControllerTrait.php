<?php
declare(strict_types=1);

namespace Crud\Controller;

use Cake\Controller\Component;
use Cake\Controller\Exception\MissingActionException;
use Closure;

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
    protected $dispatchComponents = ['Crud' => true];

    /**
     * Reference to component which should handle the mapped action.
     *
     * @var \Controller\Component\CrudComponent|null
     */
    protected $mappedComponent;

    /**
     * Get the closure for action to be invoked by ControllerFactory.
     *
     * @return \Closure
     * @throws \Cake\Controller\Exception\MissingActionException
     */
    public function getAction(): Closure
    {
        try {
            return parent::getAction();
        } catch (MissingActionException $e) {
            $this->mappedComponent = $this->_isActionMapped();
            if ($this->mappedComponent) {
                return function () {
                    // Dummy closure without arguments.
                    // This is to prevent the ControllerFactory from trying to type cast the method args.
                    // invokeAction() below simply ignores the $action argument for Crud mapped actions
                    // and calls CrudComponent::execute() directly.
                };
            }
        }

        throw $e;
    }

    /**
     * Dispatches the controller action.
     *
     * If a controller method with required name does not exist we attempt to execute Crud action.
     *
     * @param \Closure $action The action closure.
     * @param array $args The arguments to be passed when invoking action.
     * @return void
     * @throws \UnexpectedValueException If return value of action is not `null` or `ResponseInterface` instance.
     */
    public function invokeAction(Closure $action, array $args): void
    {
        if ($this->mappedComponent) {
            $this->response = $this->mappedComponent->execute(
                $this->request->getParam('action'),
                array_values($this->getRequest()->getParam('pass'))
            );

            return;
        }

        parent::invokeAction($action, $args);
    }

    /**
     * Check if an action can be dispatched using CRUD.
     *
     * @return \Cake\Controller\Component|null The component instance if action is
     *  mapped else `null`.
     */
    protected function _isActionMapped(): ?Component
    {
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

        return null;
    }
}
