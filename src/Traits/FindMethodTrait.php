<?php
declare(strict_types=1);

namespace Crud\Traits;

use Cake\Datasource\EntityInterface;
use Crud\Event\Subject;

trait FindMethodTrait
{
    /**
     * Change the find() method
     *
     * If `$method` is NULL the current value is returned
     * else the `findMethod` is changed
     *
     * @param string|array|null $method Method name as string or array where
     * key is finder name and value is find options.
     * @return string|array|$this
     */
    public function findMethod($method = null)
    {
        if ($method === null) {
            return $this->getConfig('findMethod');
        }

        $this->setConfig('findMethod', $method);

        return $method;
    }

    /**
     * Extracts the finder name and options out of the "findMethod" option.
     *
     * @return array An array containing in the first position the finder name
     *   and in the second the options to be passed to it.
     */
    protected function _extractFinder(): array
    {
        $finder = $this->findMethod();
        $options = [];
        if (is_array($finder)) {
            $options = (array)current($finder);
            $finder = key($finder);
        }

        return [$finder, $options];
    }

    /**
     * Find a record from the ID
     *
     * @param string|null $id Record id
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Datasource\EntityInterface
     * @throws \Exception
     */
    protected function _findRecord(?string $id, Subject $subject): EntityInterface
    {
        $repository = $this->_table();

        [$finder, $options] = $this->_extractFinder();
        $query = $repository->find($finder, $options);
        /**
         * @psalm-suppress PossiblyInvalidArgument
         * @psalm-suppress InvalidArrayOffset
         */
        $query->where([current($query->aliasField($repository->getPrimaryKey())) => $id]);

        $subject->set([
            'repository' => $repository,
            'query' => $query,
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
     * @param string|null $id Record id
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     * @throws \Exception
     */
    protected function _notFound(?string $id, Subject $subject): void
    {
        $subject->set(['success' => false]);
        $this->_trigger('recordNotFound', $subject);

        $message = $this->message('recordNotFound', compact('id'));
        /** @psalm-var class-string<\Exception> $exceptionClass */
        $exceptionClass = $message['class'];
        throw new $exceptionClass($message['text'], $message['code']);
    }
}
