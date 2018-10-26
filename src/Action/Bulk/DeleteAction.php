<?php
namespace Crud\Action\Bulk;

use Cake\Controller\Controller;
use Cake\ORM\Query;

/**
 * Handles Bulk 'Delete' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DeleteAction extends BaseAction
{
    /**
     * Constructor
     *
     * @param \Cake\Controller\Controller $Controller Controller instance
     * @param array $config Default settings
     * @return void
     */
    public function __construct(Controller $Controller, $config = [])
    {
        $this->_defaultConfig['messages'] = [
            'success' => [
                'text' => 'Delete completed successfully'
            ],
            'error' => [
                'text' => 'Could not complete deletion'
            ]
        ];
        $this->_defaultConfig['cascade'] = false;

        parent::__construct($Controller, $config);
    }

    /**
     * @param \Cake\ORM\Query|null $query The query to act upon
     *
     * @return bool
     */
    protected function _deleteAll(Query $query = null)
    {
        $query = $query->delete();
        $statement = $query->execute();
        $statement->closeCursor();

        return (bool)$statement->rowCount();
    }

    /**
     * @param \Cake\ORM\Query|null $query The query to act upon
     *
     * @throws \Exception
     * @return bool
     */
    protected function _cascadeDeletes(Query $query = null)
    {
        $repository = $this->_table();

        $connection = $repository->getConnection();
        return $connection->transactional(function () use ($repository, $query) {
            $result = true;
            foreach ($query as $entity) {
                $result = $result && (bool)$repository->delete($entity);

                if (!$result) {
                    return $result;
                }
            }

            return $result;
        });
    }

    /**
     * Handle a bulk delete
     *
     * @param \Cake\ORM\Query|null $query The query to act upon
     * @return bool
     */
    protected function _bulk(Query $query = null)
    {
        if ((bool)$this->getConfig('cascade')) {
            return $this->_cascadeDeletes($query);
        }

        return $this->_deleteAll($query);
    }
}
