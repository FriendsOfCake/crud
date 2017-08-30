<?php
// @deprecated Use class BaseObject instead of Object PHP 7.2+; Will be removed in CRUD 6.x.
if (PHP_VERSION_ID < 72000) {
    class_alias('Crud\Core\BaseObject', 'Crud\Core\Object');
    trigger_error('Object is a reserved word in PHP 7.2+ and cannot be used as a class name.', E_USER_NOTICE);
}
