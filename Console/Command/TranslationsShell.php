<?php
App::uses('AppShell', 'Console/Command');
App::uses('TranslationsEvent', 'Crud.Controller/Event');

/**
 * TranslationsShell
 */
class TranslationsShell extends AppShell {

/**
 * _messages
 *
 * @var array
 */
	protected $_messages = array();

/**
 * _path
 *
 * @var string
 */
	protected $_path = '';

/**
 * _strings
 *
 * @var array
 */
	protected $_strings = array();

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
 * generate
 *
 * @return void
 */
	public function generate() {
		if (!$this->_messages) {
			$this->_initializeMessages();
		}

		$models = $this->_getModels();

		if (!$models) {
			return;
		}

		$this->hr();
		$this->out(sprintf('Generating translation strings for models: %s.', implode($models, ', ')));
		$this->out('');

		$path = $this->path();

		if (file_exists($path)) {
			$this->_strings = array_map('rtrim', file($path));
		} else {
			$this->_strings[] = '<?php';
		}

		$this->_generateTranslations(false);
		foreach ($models as $model) {
			$this->_generateTranslations($model);
		}

		return $this->_writeFile();
	}

/**
 * _generateTranslations
 *
 * @param mixed $modelName
 * @return void
 */
	protected function _generateTranslations($modelName) {
		if ($modelName) {
			$message = "$modelName CRUD Component translations";
		} else {
			$message = "Common CRUD Component translations";
		}

		$this->_addDocBlock($message);
		foreach ($this->_messages as $message) {
			$type = strpos($message, '}') ? 'model' : 'common';

			if ($type === 'model' && $modelName || $type === 'common' && !$modelName) {
				$message = String::insert($message, array('name' => $modelName), array('before' => '{', 'after' => '}'));
				$string = "__d('crud', '$message');";

				if (in_array($string, $this->_strings)) {
					$this->out('<info>Skipping:</info> ' . $message, 1, Shell::VERBOSE);
				} else {
					$this->out('<success>Adding:</success> ' . $message);
					$this->_strings[] = $string;
				}
			}
		}
	}

/**
 * _addDocBlock
 *
 * @param mixed $message
 * @return void
 */
	protected function _addDocBlock($message) {
		$message = " * $message";

		if (in_array($message, $this->_strings)) {
			return false;
		}

		$this->_strings[] = '';
		$this->_strings[] = '/**';
		$this->_strings[] = $message;
		$this->_strings[] = ' */';
		return true;
	}

/**
 * _getModels
 *
 * @return void
 */
	protected function _getModels() {
		$objectType = 'Model';
		$models = array();

		if ($this->args) {
			foreach ($this->args as $arg) {
				preg_match('@Plugin/([^/]*)/?(?:Model/([^/]*))?@', $arg, $match);

				if (!empty($match[2])) {
					$models[] = str_replace('.php', '', $match[2]);
				} elseif (!empty($match[1])) {
					$plugin = $match[1];
					$models = array_merge($models, App::objects("$plugin.Model"));
				}
			}
		} else {
			$models = App::objects('Model');
		}

		return $models;
	}

/**
 * path
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
 * _initializeMessages
 *
 * @return void
 */
	protected function _initializeMessages() {
		$event = new TranslationsEvent();
		$defaults = $event->getDefaults();
		foreach ($defaults as $key => $array) {
			if (!is_array($array)) {
				continue;
			}
			foreach ($array as $subkey => $row) {
				if (!is_array($row) || !isset($row['message'])) {
					continue;
				}
				$this->_messages["$key.$subkey"] = $row['message'];
			}
		}
	}

/**
 * _writeFile
 *
 * @return void
 */
	protected function _writeFile() {
		$path = $this->path();

		$lines = implode($this->_strings, "\n") . "\n";
		$file = new File($path, true, 0644);
		$file->write($lines);

		$this->out(str_replace('APP', '', $path) . ' updated');
		$this->hr();

		return $path;
	}
}
