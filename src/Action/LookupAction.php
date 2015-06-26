<?php
namespace Crud\Action;

use Crud\Traits\SerializeTrait;
use Crud\Traits\ViewTrait;
use Crud\Traits\ViewVarTrait;

/**
 * Handles 'Lookup' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class LookupAction extends BaseAction
{

    use SerializeTrait;
    use ViewTrait;
    use ViewVarTrait;

    /**
     * Default settings for 'lookup' actions
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => true,
        'scope' => 'table',
        'findMethod' => 'list'
    ];

    /**
     * Handle a lookup event
     *
     * @return void
     */
    protected function _handle()
    {
        $query = $this->_table()->find($this->config('findMethod'), $this->_getFindConfig());
        $subject = $this->_subject(['success' => true, 'query' => $query]);

        $this->_trigger('beforeLookup', $subject);
        $subject->set(['entities' => $this->_controller()->paginate($subject->query)]);
        $this->_trigger('afterLookup', $subject);

        $this->_trigger('beforeRender', $subject);
    }

    /**
     * Get the query configuration (2nd param to find($y, $y))
     *
     * @return array
     */
    protected function _getFindConfig()
    {
        $request = $this->_request();

        $config = (array)$this->config('findConfig');
        if ($idField = $request->query('id')) {
            $config['keyField'] = $idField;
        }

        if ($valueField = $request->query('value')) {
            $config['valueField'] = $valueField;
        }

        return $config;
    }
}
