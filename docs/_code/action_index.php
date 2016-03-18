<?php
namespace Crud\Action;

class Index extends BaseAction
{

    /**
     * Generic handler for all HTTP verbs
     *
     * @return void
     */
    protected function _handle()
    {
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
