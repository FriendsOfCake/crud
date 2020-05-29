<?php
// @codingStandardsIgnoreFile

use Cake\Filesystem\Folder;

$findRoot = function () {
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
};

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
define('ROOT', $findRoot());
define('APP_DIR', 'App');
define('WEBROOT_DIR', 'webroot');
define('APP', ROOT . '/tests/App/');
define('CONFIG', ROOT . '/tests/config/');
define('WWW_ROOT', ROOT . DS . WEBROOT_DIR . DS);
define('TESTS', ROOT . DS . 'tests' . DS);
define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);

require ROOT . '/vendor/autoload.php';
require CORE_PATH . 'config/bootstrap.php';

Cake\Core\Configure::write('App', ['namespace' => 'Crud\Test\App']);
Cake\Core\Configure::write('debug', true);

$TMP = new Folder(TMP);
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

Cake\Cache\Cache::setConfig($cache);
Cake\Core\Configure::write('Session', [
    'defaults' => 'php'
]);

// Ensure default test connection is defined
if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

Cake\Datasource\ConnectionManager::setConfig('test', [
    'url' => getenv('db_dsn'),
    'timezone' => 'UTC'
]);

loadPHPUnitAliases();
