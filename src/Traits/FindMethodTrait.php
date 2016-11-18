<?php
namespace Crud\Traits;

use Crud\Event\Subject;

trait FindMethodTrait
{

    /**
     * Change the find() method
     *
     * If `$method` is NULL the current value is returned
     * else the `findMethod` is changed
     *
     * @param string|null $method Method name
     * @return string
     */
    public function findMethod($method = null)
    {
        if ($method === null) {
            return $this->config('findMethod');
        }

        return $this->config('findMethod', $method);
    }

    /**
     * Find a record from the ID
     *
     * @param string $id Record id
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\ORM\Entity
     */
    protected function _findRecord($id, Subject $subject)
    {
        $repository = $this->_table();

        $query = $repository->find($this->findMethod());
        $query->where([current($query->aliasField($repository->primaryKey())) => $id]);

        $subject->set([
            'repository' => $repository,
            'query' => $query
        ]);

        $this->_trigger('beforeFind', $subject);
        $entity = $subject->query->first();

        if (!$entity) {
            $this->_notFound($id, $subject);
        }

        $subject->set(['entity' => $entity, 'success' => true]);
        $this->_trigger('afterFind', $subject);

        return $entity;
    }

    /**
     * Throw exception if a record is not found
     *
     * @param string $id Record id
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     * @throws \Exception
     */
    protected function _notFound($id, Subject $subject)
    {
        $subject->set(['success' => false]);
        $this->_trigger('recordNotFound', $subject);

        $message = $this->message('recordNotFound', compact('id'));
        $exceptionClass = $message['class'];
        throw new $exceptionClass($message['text'], $message['code']);
    }
}
