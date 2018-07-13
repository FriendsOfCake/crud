<?php
if (!class_exists('Cake\Http\Exception\MethodNotAllowedException')) {
    class_alias(
        'Cake\Network\Exception\MethodNotAllowedException',
        'Cake\Http\Exception\MethodNotAllowedException'
    );
}

if (!class_exists('Cake\Http\Exception\NotImplementedException')) {
    class_alias(
        'Cake\Network\Exception\NotImplementedException',
        'Cake\Http\Exception\NotImplementedException'
    );
}

if (!class_exists('Cake\Http\Exception\BadRequestException')) {
    class_alias(
        'Cake\Network\Exception\BadRequestException',
        'Cake\Http\Exception\BadRequestException'
    );
}

if (!class_exists('Cake\Http\Exception\NotFoundException')) {
    class_alias(
        'Cake\Network\Exception\NotFoundException',
        'Cake\Http\Exception\NotFoundException'
    );
}
