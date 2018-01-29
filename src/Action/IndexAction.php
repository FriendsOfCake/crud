<?php
namespace Crud\Action;

use Crud\Traits\FindMethodTrait;
use Crud\Traits\SerializeTrait;
use Crud\Traits\ViewTrait;
use Crud\Traits\ViewVarTrait;

/**
 * Handles 'Index' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class IndexAction extends BaseAction
{

    use FindMethodTrait;
    use SerializeTrait;
    use ViewTrait;
    use ViewVarTrait;

    /**
     * Default settings for 'index' actions
     *
     * @var array
     */
    protected $_defaultConfig = [
        'enabled' => true,
        'scope' => 'table',
        'findMethod' => 'all',
        'view' => null,
        'viewVar' => null,
        'serialize' => [],
        'api' => [
            'success' => [
                'code' => 200
            ],
            'error' => [
                'code' => 400
            ]
        ]
    ];

    /**
     * Generic handler for all HTTP verbs
     *
     * @return void
     */
    protected function _handle()
    {
        list($finder, $options) = $this->_extractFinder();
        $query = $this->_table()->find($finder, $options);
        $subject = $this->_subject(['success' => true, 'query' => $query]);

        $this->_trigger('beforePaginate', $subject);
        $items = $this->_controller()->paginate($subject->query);
        $subject->set(['entities' => $items]);

        $this->_trigger('afterPaginate', $subject);
        $this->_trigger('beforeRender', $subject);
    }
}
