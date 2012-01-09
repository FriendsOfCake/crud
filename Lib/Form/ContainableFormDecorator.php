<?php
App::uses('BaseFormDecorator', 'Crud.Form');

class ContainableFormDecorator extends BaseFormDecorator {
    public function beforeFind($Controller, $query) {
        return array_merge($query, array('contain' => $this->_settings));
    }
}