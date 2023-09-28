<?php
namespace Crud\Action;

class Index extends BaseAction
{
    /**
     * Generic handler for all HTTP verbs
     *
     * @return \Cake\Http\Response|null
     */
    protected function _handle(): ?Response
    {
        [$finder, $options] = $this->_extractFinder();
        $query = $this->_model()->find($finder, ...$options);
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
