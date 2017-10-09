<?php

namespace Crud\Traits;

use Crud\Event\Subject;

trait StoppableTrait
{
    /**
     * Stopped callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Network\Response
     */
    protected function _stopped(Subject $subject)
    {
        if (!isset($subject->success)) {
            $subject->success = false;
        }

        if ($subject->success) {
            return $this->_success($subject);
        }

        $subject->set(['success' => false]);
        $this->setFlash('error', $subject);

        return $this->_redirect($subject, ['action' => 'index']);
    }
}
