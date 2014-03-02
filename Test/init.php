<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__DIR__)));
define('APP_DIR', basename(dirname(__DIR__)));
define('WEBROOT_DIR', 'webroot');
define('APP', ROOT . DS . APP_DIR . DS);
define('WWW_ROOT', ROOT . DS . WEBROOT_DIR . DS);
define('TESTS', ROOT . DS . 'Test' . DS);
define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);

require dirname(__DIR__) . '/vendor/cakephp/cakephp/src/basics.php';
require dirname(__DIR__) . '/vendor/autoload.php';

Cake\Core\Configure::write('App', [
	'namespace' => 'App'
]);

Cake\Core\Plugin::load('Crud', ['path' => './']);
