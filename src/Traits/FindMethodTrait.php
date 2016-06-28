<?php
namespace Crud\Traits;

use Crud\Event\Subject;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;

trait FindMethodTrait
{

    /**
     * Change the find() method
     *
     * If `$method` is NULL the current value is returned
     * else the `findMethod` is changed
     *
     * @param mixed $method Method name
     * @return mixed
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
     * @throws \Cake\Datasource\Exception\InvalidPrimaryKeyException When $primaryKey has an
     *      incorrect number of elements.
     */
    protected function _findRecord($id, Subject $subject)
    {
        $repository = $this->_table();

        $key = (array)$repository->primaryKey();
        $alias = $repository->alias();
        foreach ($key as $index => $keyname) {
            $key[$index] = $alias . '.' . $keyname;
        }
        $primaryKey = (array)$id;
        if (count($key) !== count($primaryKey)) {
            $primaryKey = $primaryKey ?: [null];
            $primaryKey = array_map(function ($key) {
                return var_export($key, true);
            }, $primaryKey);
            throw new InvalidPrimaryKeyException(sprintf(
                'Record not found in table "%s" with primary key [%s]',
                $repository,
                implode($primaryKey, ', ')
            ));
        }
        $conditions = array_combine($key, $primaryKey);

        $query = $repository->find($this->findMethod());
        $query->where($conditions);

        $subject->set([
            'repository' => $repository,
            'query' => $query
        ]);

        $this->_trigger('beforeFind', $subject);
        $entity = $subject->query->first();

        if (!$entity) {
            return $this->_notFound($id, $subject);
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
