<?php
declare(strict_types=1);

namespace Crud\Controller;

use Cake\Controller\Exception\MissingActionException;
use Closure;

/**
 * Enable Crud to catch MissingActionException and attempt to generate response
 * using Crud.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 *
 * @property \Crud\Controller\Component\CrudComponent $Crud
 */
trait ControllerTrait
{
    /**
     * View classes map for content type negotiation.
     *
     * @var array
     */
    protected array $viewClasses = [];

    /**
     * Whether current action is mapped to a Crud action.
     *
     * @var bool
     */
    protected bool $mappedAction = false;

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
            $this->mappedAction = $this->Crud->isActionMapped($this->request->getParam('action'));

            if ($this->mappedAction) {
                return function (): void {
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
     * If the action is mapped to a Crud action we execute it.
     *
     * @param \Closure $action The action closure.
     * @param array $args The arguments to be passed when invoking action.
     * @return void
     */
    public function invokeAction(Closure $action, array $args): void
    {
        if ($this->mappedAction) {
            $this->response = $this->Crud->execute(
                $this->request->getParam('action'),
                array_values($this->getRequest()->getParam('pass'))
            );

            return;
        }

        parent::invokeAction($action, $args);
    }

    /**
     * Set view classes map for content negotiation.
     *
     * @param array<string, class-string<\Cake\View\View>> $map View class map.
     * @return void
     */
    public function setViewClasses(array $map): void
    {
        $this->viewClasses = $map;
    }

    /**
     * Get the View classes this controller can perform content negotiation with.
     *
     * Each view class must implement the `getContentType()` hook method
     * to participate in negotiation.
     *
     * This overrides the Controller::viewClasses() of core.
     *
     * @see Cake\Http\ContentTypeNegotiation
     * @return array<string, class-string<\Cake\View\View>>
     */
    public function viewClasses(): array
    {
        return $this->viewClasses;
    }
}
