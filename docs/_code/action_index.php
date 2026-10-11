<?php
namespace Crud\Action;

class Index extends BaseAction
{
    /**
     * Generic handler for all HTTP verbs
     *
     * @return \Cake\Http\Response|null
     */
    protected function handleRequest(): ?Response
    {
        [$finder, $options] = $this->extractFinder();
        $query = $this->model()->find($finder, ...$options);
        $subject = $this->subject(['success' => true, 'query' => $query]);

        $this->trigger('beforePaginate', $subject);
        try {
            $items = $this->controller()->paginate($subject->query);
        } catch (NotFoundException $e) {
            /** @var \Cake\Core\Exception\CakeException $previous */
            $previous = $e->getPrevious();
            $pagingParams = $previous->getAttributes()['pagingParams'];

            $url = Router::reverseToArray($this->request());
            $url['?']['page'] = $pagingParams['pageCount'];

            return $this->controller()->redirect($url);
        }

        $subject->set(['entities' => $items]);

        $this->trigger('afterPaginate', $subject);
        $this->trigger('beforeRender', $subject);

        return null;
    }
}
