<?php
namespace Crud\Action\Bulk;

use Cake\Controller\Controller;
use Cake\ORM\Query;
use Crud\Error\Exception\ActionNotConfiguredException;

/**
 * Handles Bulk 'SetValue' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class SetValueAction extends BaseAction
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
                'text' => 'Set value successfully'
            ],
            'error' => [
                'text' => 'Could not set value'
            ]
        ];
        $this->_defaultConfig['value'] = null;

        parent::__construct($Controller, $config);
    }

    /**
     * Handle a bulk event
     *
     * @return \Cake\Http\Response
     * @throws \Crud\Error\Exception\ActionNotConfiguredException
     */
    protected function _handle()
    {
        $field = $this->getConfig('field');
        if (empty($field)) {
            throw new ActionNotConfiguredException('No field value specified');
        }

        return parent::_handle();
    }

    /**
     * Handle a bulk value set
     *
     * @param \Cake\ORM\Query|null $query The query to act upon
     * @return bool
     */
    protected function _bulk(Query $query = null)
    {
        $field = $this->getConfig('field');
        $value = $this->getConfig('value');
        $query->update()->set([$field => $value]);
        $statement = $query->execute();
        $statement->closeCursor();

        return (bool)$statement->rowCount();
    }
}
