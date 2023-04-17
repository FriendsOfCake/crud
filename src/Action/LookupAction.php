<?php
declare(strict_types=1);

namespace Crud\Action;

use Crud\Traits\FindMethodTrait;
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
    use FindMethodTrait;
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
        'findMethod' => 'list',
    ];

    /**
     * Handle a lookup event
     *
     * @return void
     */
    protected function _handle(): void
    {
        [$finder, $options] = $this->_extractFinder();
        $options = array_merge($options, $this->_getFindConfig());
        $query = $this->_table()->find($finder, $options);
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
    protected function _getFindConfig(): array
    {
        $request = $this->_request();

        $columns = $this->_table()->getSchema()->columns();
        $config = (array)$this->getConfig('findConfig');

        $idField = $request->getQuery('key_field') ?: $request->getQuery('id');
        if (in_array($idField, $columns)) {
            $config['keyField'] = $idField;
        }

        $valueField = $request->getQuery('value_field') ?: $request->getQuery('value');
        if (in_array($valueField, $columns)) {
            $config['valueField'] = $valueField;
        }

        return $config;
    }
}
