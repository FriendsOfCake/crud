<?php
declare(strict_types=1);

namespace Crud\Action\Bulk;

use Cake\Controller\Controller;
use Cake\Database\Query;
use Cake\ORM\Query\DeleteQuery;

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
    public function __construct(Controller $Controller, array $config = [])
    {
        $this->_defaultConfig['queryType'] = Query::TYPE_DELETE;

        $this->_defaultConfig['messages'] = [
            'success' => [
                'text' => 'Delete completed successfully',
            ],
            'error' => [
                'text' => 'Could not complete deletion',
            ],
        ];

        parent::__construct($Controller, $config);
    }

    /**
     * Handle a bulk delete
     *
     * @param \Cake\Database\Query $query The query to act upon
     * @return bool
     */
    protected function _bulk(Query $query): bool
    {
        assert($query instanceof DeleteQuery);

        return (bool)$query->rowCountAndClose();
    }
}
