<?php
declare(strict_types=1);

// @deprecated Add backwards compat alias. "Object" is protected keyword in PHP 7.2
if (PHP_VERSION_ID < 70200) {
    class_alias('Crud\Core\BaseObject', 'Crud\Core\Object');
}
