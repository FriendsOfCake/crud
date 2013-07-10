<?php

App::uses('AppShell', 'Console/Command');
App::uses('CrudSubject', 'Crud.Controller/Crud');
App::uses('TranslationsListener', 'Crud.Controller/Crud/Listener');

/**
 * TranslationsShell
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Christian Winther, 2013
 */
class TranslationsShell extends AppShell {

/**
 * The array of raw stings to be written to the output file
 *
 * @var array
 */
	public $lines = array();

/**
 * The path to write the output file to
 *
 * @var string
 */
	protected $_path = '';

/**
 * Gets the option parser instance and configures it.
 * By overriding this method you can configure the ConsoleOptionParser before returning it.
 *
 * @return ConsoleOptionParser
 * @link http://book.cakephp.org/2.0/en/console-and-shells.html#Shell::getOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		return $parser
			->addSubCommand('generate', array(
				'help' => 'Generate the translation strings for CRUD component usage'
			));
	}

/**
 * Create or update the file containing the translation strings for CRUD component usage
 *
 * @return void
 */
	public function generate() {
		$controllers = $this->_getControllers();
		if (!$controllers) {
			$this->out('<warning>No controllers found to be processed</warning>');
			return;
		}

		$this->hr();
		$this->out(sprintf('Processing translation strings for controllers: %s.', implode($controllers, ', ')));
		$this->out('');

		$path = $this->path();

		if (file_exists($path)) {
			$this->lines = array_map('rtrim', file($path));
		} else {
			$this->lines[] = '<?php';
		}

		foreach ($controllers as $name) {
			$this->_processController($name);
		}

		return $this->_writeFile();
	}

/**
 * _addDocBlock
 *
 * Add a doc block to the lines property with the passed message appropriately formatted
 * If the doc block already exists - return false
 *
 * @param string $message
 * @return bool success
 */
	protected function _addDocBlock($message) {
		$message = " * $message";

		if (in_array($message, $this->lines)) {
			return false;
		}

		$this->lines[] = '';
		$this->lines[] = '/**';
		$this->lines[] = $message;
		$this->lines[] = ' */';
		return true;
	}

/**
 * _getControllers
 *
 * If no arguments are passed to the cli call, return all App controllers
 * Otherwise, assume the arguments are a list of file paths to plugin model dirs or an individual plugin model
 *
 * @return array
 */
	protected function _getControllers() {
		$objectType = 'Controller';
		$controllers = array();

		if ($this->args) {
			foreach ($this->args as $arg) {
				preg_match('@Plugin/([^/]*)/?(?:Controller/([^/]*))?@', $arg, $match);

				if (!empty($match[2])) {
					$controllers[] = str_replace('.php', '', $match[2]);
				} elseif (!empty($match[1])) {
					$plugin = $match[1];
					$controllers = array_merge($controllers, App::objects("$plugin.Controller"));
				}
			}
		} else {
			$controllers = App::objects('Controller');
		}

		foreach ($controllers as &$controller) {
			$controller = preg_replace('/Controller$/', '', $controller);
		}

		return $controllers;
	}

/**
 * path
 *
 * Set or retrieve the path to write the output file to
 * Defaults to APP/Config/i18n_crud.php
 *
 * @param mixed $path
 * @return string
 */
	public function path($path = null) {
		if ($path) {
			$this->_path = $path;
		} elseif (!$this->_path) {
			$this->_path = APP . 'Config/i18n_crud.php';
		}
		return $this->_path;
	}

/**
 * _loadController
 *
 * @param string $name
 * @return Controller
 */
	protected function _loadController($name, $plugin) {
		$className = $name . 'Controller';
		$prefix = rtrim($plugin, '.');

		if ($className === $prefix . 'AppController') {
			$this->out("<info>Skipping:</info> $className", 1, Shell::VERBOSE);
			return;
		}

		App::uses($className, $plugin . 'Controller');

		if (!class_exists($className)) {
			$this->out("<info>Skipping:</info> $className, class could not be loaded", 1, Shell::VERBOSE);
			return;
		}

		$Controller = new $className();
		$Controller->constructClasses();
		$Controller->startupProcess();

		if (!$Controller->uses) {
			$this->out("<info>Skipping:</info> $className, doesn't use any models", 1, Shell::VERBOSE);
			return;
		}

		if (!isset($Controller->Crud)) {
			$this->out("<info>Skipping:</info> $className, doesn't use Crud component", 1, Shell::VERBOSE);
			return;
		}

		return $Controller;
	}

/**
 * processController
 *
 * For the given controller name, initialize the crud component and process each action.
 * Create a listener for the setFlash event to log the flash message details.
 *
 * @param string $name Controller name
 */
	protected function _processController($name) {
		list($plugin, $name) = pluginSplit($name, true);
		$prefix = rtrim($plugin, '.');

		$Controller = $this->_loadController($name, $plugin);

		if (!$Controller) {
			return;
		}

		$this->_addDocBlock("$name CRUD Component translations");

		$that = $this;
		$Controller->Crud->on('setFlash', function(CakeEvent $event) use ($that) {
			$key = $event->subject->name . ' ' . $event->subject->type;
			$message = $event->subject->params['original'];

			if (!$message) {
				return;
			}

			$string = "__d('crud', '$message');";

			if (in_array($string, $that->lines)) {
				$that->out('<info>Skipping:</info> ' . $message, 1, Shell::VERBOSE);
			} else {
				$that->out('<success>Adding:</success> ' . $message);
				$that->lines[] = $string;
			}
		});

		$actions = array_keys($Controller->Crud->config('actions'));
		foreach ($actions as $actionName) {
			$this->_processAction($actionName, $Controller);
		}
	}

/**
 * _processAction
 *
 * Process a single crud action. Initialize the action object, and trigger each
 * flash message.
 *
 * @param string $actionName crud action name
 * @param Controller $Controller instance
 */
	protected function _processAction($actionName, $Controller) {
		try {
			$Controller->Crud->initAction($actionName);
		} catch(Exception $e) {
			return;
		}

		$action = $Controller->Crud->action($actionName);
		$messages = $action->config('messages');
		if (!$messages) {
			return;
		}

		foreach (array_keys($messages) as $type) {
			if ($type === 'domain') {
				continue;
			}
			$action->setFlash($type);
		}
	}

/**
 * _writeFile
 *
 * Take the lines property, populated by the generate method - and write it
 * out to the output file path
 *
 * @return string the file path written to
 */
	protected function _writeFile() {
		$path = $this->path();

		$lines = implode($this->lines, "\n") . "\n";
		$file = new File($path, true, 0644);
		$file->write($lines);

		$this->out(str_replace('APP', '', $path) . ' updated');
		$this->hr();

		return $path;
	}
}
