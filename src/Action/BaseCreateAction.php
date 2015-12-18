<?php
namespace Crud\Action;

use Crud\Event\Subject;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\RedirectTrait;
use Crud\Traits\SaveMethodTrait;
use Crud\Traits\SerializeTrait;
use Crud\Traits\ViewTrait;
use Crud\Traits\ViewVarTrait;

/**
 * Base Create/Update Crud class
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class BaseCreateAction extends BaseAction
{

    use FindMethodTrait;
    use RedirectTrait;
    use SaveMethodTrait;
    use SerializeTrait;
    use ViewTrait;
    use ViewVarTrait;

    /**
     * HTTP GET handler
     *
     * @return void
     */
    protected function _get($id = null)
    {
        $subject = $this->_getSubject($this->_subject(), $id);
        $this->_trigger('beforeRender', $subject);
    }

    /**
     * HTTP POST handler
     *
     * @param mixed $id Record id
     * @return void|\Cake\Network\Response
     */
    protected function _post($id = null)
    {
        return $this->_createOrUpdate($id);
    }

    /**
     * HTTP PUT handler
     *
     * @param mixed $id Record id
     * @return void|\Cake\Network\Response
     */
    protected function _put($id = null)
    {
        return $this->_createOrUpdate($id);
    }

    /**
     * Create or Update a record
     *
     * @param mixed $id Record id
     * @return void|\Cake\Network\Response
     */
    protected function _createOrUpdate($id = null)
    {
        $subject = $this->_subject();
        $subject->set(['id' => $id]);
        $subject->set(['saveMethod' => $this->saveMethod()]);
        $subject->set(['saveOptions' => $this->saveOptions()]);
        $subject->set(['entity' => $this->_postEntity($subject, $id)]);

        $this->_trigger('beforeSave', $subject);

        $saveCallback = [$this->_table(), $subject->saveMethod];
        if (call_user_func($saveCallback, $subject->entity, $subject->saveOptions)) {
            return $this->_success($subject);
        }

        return $this->_error($subject);
    }


    /**
     * Post success callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Network\Response
     */
    protected function _success(Subject $subject)
    {
        $subject->set(['success' => true, 'created' => $this->config('entityCreated')]);
        $this->_trigger('afterSave', $subject);
        $this->setFlash('success', $subject);
        return $this->_redirect($subject, ['action' => 'index']);
    }

    /**
     * Post error callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function _error(Subject $subject)
    {
        $subject->set(['success' => false, 'created' => false]);
        $this->_trigger('afterSave', $subject);
        $this->setFlash('error', $subject);
        $this->_trigger('beforeRender', $subject);
    }

    /**
     * Returns the subject for GET requests
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param mixed $id Record id
     * @return void
     */
    abstract protected function _getSubject(Subject $subject, $id = null);

    /**
     * Returns the entity for POST/PUT requests
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @param mixed $id Record id
     * @return void
     */
    abstract protected function _postEntity(Subject $subject, $id = null);
}
