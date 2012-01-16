<?php
App::uses('BaseFormDecorator', 'Crud.Form');

/**
 * A decorator for easy containable configuration
 *
 * Copyright 2010-2012, Nodes ApS. (http://www.nodesagency.com/)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Nodes ApS, 2012
 */
class ContainableFormDecorator extends BaseFormDecorator {
    public function beforeFind($Controller, $query) {
        return array_merge($query, array('contain' => $this->_settings));
    }
}