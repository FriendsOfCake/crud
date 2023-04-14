<?php
declare(strict_types=1);

namespace Crud\Action;

use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Routing\Router;
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
    protected array $_defaultConfig = [
        'enabled' => true,
        'scope' => 'table',
        'findMethod' => 'all',
        'view' => null,
        'viewVar' => null,
        'serialize' => [],
        'api' => [
            'success' => [
                'code' => 200,
            ],
            'error' => [
                'code' => 400,
            ],
        ],
    ];

    /**
     * Generic handler for all HTTP verbs
     *
     * @return \Cake\Http\Response|null
     */
    protected function _handle(): ?Response
    {
        [$finder, $options] = $this->_extractFinder();
        $query = $this->_model()->find($finder, $options);
        $subject = $this->_subject(['success' => true, 'query' => $query]);

        $this->_trigger('beforePaginate', $subject);
        try {
            $items = $this->_controller()->paginate($subject->query);
        } catch (NotFoundException $e) {
            /** @var \Cake\Core\Exception\CakeException $previous */
            $previous = $e->getPrevious();
            $pagingParams = $previous->getAttributes()['pagingParams'];

            $url = Router::reverseToArray($this->_request());
            $url['?']['page'] = $pagingParams['pageCount'];

            return $this->_controller()->redirect($url);
        }

        $subject->set(['entities' => $items]);

        $this->_trigger('afterPaginate', $subject);
        $this->_trigger('beforeRender', $subject);

        return null;
    }
}
