<?php

App::uses('AppHelper', 'View/Helper');

class ApiPaginationHelper extends AppHelper {

	public $helpers = array('Paginator');

	public function beforeRender($viewFile) {
		$this->_View->viewVars['pagination'] = $this->_getPagination();
	}

/**
 * Get the query logs for all sources
 *
 * @return array
 */
	protected function _getPagination() {
		$_pagination = $this->Paginator->request->paging;
		$_pagination = $_pagination[$this->Paginator->defaultModel()];

		$extra_paginator_url_params = empty($extra_paginator_url_params) ? array() : $extra_paginator_url_params;

		$pagination = array(
			'pageCount' => $_pagination['pageCount'],
			'current' => $_pagination['page'],
			'count' => $_pagination['count']
		);

		if ($this->Paginator->hasPrev()) {
			$pagination['prev'] = $this->_getURL($this->Paginator->prev('_PREV_', array('tag' => false, 'url' => array('ext' => $this->request->ext))));
		} else {
			$pagination['prev'] = false;
		}

		if ($this->Paginator->hasNext()) {
			$pagination['next'] = $this->_getURL($this->Paginator->next('_NEXT_', array('tag' => false, 'url' => array('ext' => $this->request->ext))));
		} else {
			$pagination['next'] = false;
		}

		return $pagination;
	}

	protected function _getURL($string) {
		preg_match('#href="(.*?)"#sim', $string, $r);
		return Router::url(html_entity_decode($r[1]), true);
	}

}
