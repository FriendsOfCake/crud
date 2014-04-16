<?php
function find_root() {
	$root = dirname(__DIR__);
	if (is_dir($root . '/vendor/cakephp/cakephp')) {
		return $root;
	}

	$root = dirname(dirname(__DIR__));
	if (is_dir($root . '/vendor/cakephp/cakephp')) {
		return $root;
	}

	$root = dirname(dirname(dirname(__DIR__)));
	if (is_dir($root . '/vendor/cakephp/cakephp')) {
		return $root;
	}
}

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(dirname(dirname(__FILE__)))));
define('APP_DIR', 'App');
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

require ROOT . '/vendor/cakephp/cakephp/src/basics.php';
require ROOT . '/vendor/autoload.php';

Cake\Core\Configure::write('App', ['namespace' => 'App']);

$TMP = new \Cake\Utility\Folder(TMP);
$TMP->create(TMP . 'cache/models', 0777);
$TMP->create(TMP . 'cache/persistent', 0777);
$TMP->create(TMP . 'cache/views', 0777);

$cache = [
	'default' => [
		'engine' => 'File'
	],
	'_cake_core_' => [
		'className' => 'File',
		'prefix' => 'crud_myapp_cake_core_',
		'path' => CACHE . 'persistent/',
		'serialize' => true,
		'duration' => '+10 seconds'
	],
	'_cake_model_' => [
		'className' => 'File',
		'prefix' => 'crud_my_app_cake_model_',
		'path' => CACHE . 'models/',
		'serialize' => 'File',
		'duration' => '+10 seconds'
	]
];

Cake\Cache\Cache::config($cache);
Cake\Core\Plugin::load('Crud', ['path' => './']);

$datasources = [
	'test' => [
		'className' => 'Cake\Database\Connection',
		'driver' => 'Cake\Database\Driver\Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'crud3',
		'password' => 'crud3',
		'database' => 'crud3_test',
		'prefix' => false,
		'encoding' => 'utf8',
	]
];

$datasources['default'] = $datasources['test'];

Cake\Datasource\ConnectionManager::config($datasources);
