<?php
namespace Crud\Action\Bulk;

use Cake\Controller\Controller;
use Cake\Database\Expression\QueryExpression;
use Crud\Error\Exception\ActionNotConfiguredException;
use Cake\ORM\Query;

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
        $this->_defaultConfig['actionName'] = 'SetValue';
        $this->_defaultConfig['messages'] = [
            'success' => [
                'text' => 'Set value successfully'
            ],
            'error' => [
                'text' => 'Could not set value'
            ]
        ];
        $this->_defaultConfig['value'] = null;
        return parent::__construct($controller, $config);
    }

    /**
     * Handle a bulk event
     *
     * @return \Cake\Network\Response
     */
    protected function _handle()
    {
        $field = $this->config('field');
        if (empty($field)) {
            throw new ActionNotConfiguredException('No field value specified');
        }

        return parent::_handle();
    }

    /**
     * Handle a bulk value set
     *
     * @param \Cake\ORM\Query $query The query to act upon
     * @return boolean
     */
    protected function _bulk(Query $query = null)
    {
        $field = $this->config('field');
        $value = $this->config('value');
        $query->update()->set([$field => $value]);
        $statement = $query->execute();
        $statement->closeCursor();
        return $statement->rowCount();
    }
}
