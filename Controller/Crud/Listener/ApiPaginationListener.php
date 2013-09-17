<?php

App::uses('CrudListener', 'Crud.Controller/Crud');

/**
 * When loaded Crud API Pagination Listener will include
 * pagination information in the response
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ApiPaginationListener extends CrudListener {

/**
 * Returns a list of all events that will fire in the controller during its lifecycle.
 * You can override this function to add you own listener callbacks
 *
 * We attach at priority 10 so normal bound events can run before us
 *
 * @return array
 */
	public function implementedEvents() {
		return array(
			'Crud.beforeRender' => array('callable' => 'beforeRender', 'priority' => 75)
		);
	}

/**
 * Appends the pagination information to the JSON or XML output and HTTP Response Headers
 *
 * @param CakeEvent $event
 * @return void
 */
	public function beforeRender(CakeEvent $event) {
        $request = $this->_request();
		if (!$request->is('api')) {
			return;
		}

		$_pagination = $request->paging;
		if (empty($_pagination) || !array_key_exists($event->subject->modelClass, $_pagination)) {
			return;
		}

		$_pagination = $_pagination[$event->subject->modelClass];

		$pagination = array(
			'page_count' => $_pagination['pageCount'],
			'current_page' => $_pagination['page'],
			'has_next_page' => $_pagination['nextPage'],
			'has_prev_page' => $_pagination['prevPage'],
			'count' => $_pagination['count'],
			'limit' => $_pagination['limit']
		);

		$this->_action()->config('serialize.pagination', 'pagination');
		$this->_controller()->set('pagination', $pagination);

        /* Add pagination in http response header Link */
        $this->controller = $this->_controller();
        $options =  $_pagination['prevPage'] +  $_pagination['nextPage']*2;

        //if options is 10 or 11
        if($options % 2){
            $links[]=$this->createLink($this->getPageUrl(),'first');
            $links[]=$this->createLink($this->getPageUrl($_pagination['page']-1),'prev');
        }

        //if options is 01 or 11
        if($options >= 2){
            $links[]=$this->createLink($this->getPageUrl($_pagination['page']+1),'next');
            $links[]=$this->createLink($this->getPageUrl($_pagination['pageCount']),'last');
        }
        if($options){//Not paging
            $this->controller->response->header('Link', implode(',',$links));
        }
	}

/**
 * Returns a string with the HTTP Response Link format
 *
 *
 * @return string
 */
  public function createLink($url,$type){
    $format ='<%s>;rel="%s"';
    return sprintf($format,$url,$type);
  }

/**
 * Returns a router URL for pagination
 *
 *
 * @return string
 */
  public function getPageUrl($index = NULL){
      $this->controller = $this->_controller();
      if(is_null($index)) return Router::url(array('ext' => $this->controller->RequestHandler->ext),true);
      return Router::url(array('?' => "page=$index",'ext' => $this->controller->RequestHandler->ext),true);
  }
}
