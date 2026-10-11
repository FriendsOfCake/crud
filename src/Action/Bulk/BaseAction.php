<?php
declare(strict_types=1);

namespace Crud\Action\Bulk;

use Cake\Database\Query;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Crud\Action\BaseAction as CrudBaseAction;
use Crud\Event\Subject;
use Crud\Traits\FindMethodTrait;
use Crud\Traits\RedirectTrait;

/**
 * Handles 'Bulk' Crud actions
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
abstract class BaseAction extends CrudBaseAction
{
    use FindMethodTrait;
    use RedirectTrait;

    /**
     * Default settings for 'base' actions
     *
     * @var array
     */
    protected array $defaultConfig = [
        'enabled' => true,
        'scope' => 'bulk',
        'queryType' => Query::TYPE_UPDATE,
        'messages' => [
            'success' => [
                'text' => 'Bulk action successfully completed',
            ],
            'error' => [
                'text' => 'Could not complete bulk action',
            ],
        ],
    ];

    /**
     * Handle a bulk event
     *
     * @return \Cake\Http\Response|null
     */
    protected function handleRequest(): ?Response
    {
        $ids = $this->processIds();
        $subject = $this->constructSubject($ids);

        $event = $this->trigger('beforeBulk', $subject);
        if ($event->isStopped()) {
            return $this->stopped($subject);
        }

        if ($this->bulk($subject->query)) {
            $this->success($subject);
        } else {
            $this->error($subject);
        }

        return $this->redirect($subject, ['action' => 'index']);
    }

    /**
     * Retrieves a list of ids to process
     *
     * @return array
     */
    protected function processIds(): array
    {
        $ids = $this->controller()->getRequest()->getData('id');

        if (!is_array($ids)) {
            throw new BadRequestException('Bad request data');
        }

        $all = Hash::get($ids, '_all', false);
        unset($ids['_all']);

        if ($all) {
            foreach ($ids as $key => $value) {
                $ids[$key] = 1;
            }
            $ids = array_keys($ids);
        }

        return array_values(array_filter($ids));
    }

    /**
     * Setup a query object for retrieving entities
     *
     * @param array $ids An array of ids to retrieve
     * @return \Crud\Event\Subject
     */
    protected function constructSubject(array $ids): Subject
    {
        $repository = $this->model();
        assert($repository instanceof Table);

        $method = strtolower($this->getConfig('queryType')) . 'Query';
        $primaryKey = $repository->getPrimaryKey();

        $query = $repository->{$method}()
            ->where(fn($exp) => $exp->in($primaryKey, $ids));

        $subject = $this->subject();
        $subject->set([
            'ids' => $ids,
            'query' => $query,
            'repository' => $repository,
        ]);

        return $subject;
    }

    /**
     * Success callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function success(Subject $subject): void
    {
        $subject->set(['success' => true]);
        $this->trigger('afterBulk', $subject);

        $this->setFlash('success', $subject);
    }

    /**
     * Error callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return void
     */
    protected function error(Subject $subject): void
    {
        $subject->set(['success' => false]);
        $this->trigger('afterBulk', $subject);

        $this->setFlash('error', $subject);
    }

    /**
     * Stopped callback
     *
     * @param \Crud\Event\Subject $subject Event subject
     * @return \Cake\Http\Response|null
     */
    protected function stopped(Subject $subject): ?Response
    {
        $subject->set(['success' => false]);
        $this->setFlash('error', $subject);

        return $this->redirect($subject, ['action' => 'index']);
    }

    /**
     * Handle a bulk event.
     *
     * @param \Cake\Database\Query $query The query to act upon
     * @return bool
     */
    abstract protected function bulk(Query $query): bool;
}
