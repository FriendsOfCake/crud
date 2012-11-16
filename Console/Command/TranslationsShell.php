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
			$event = new TranslationsEvent();
			$defaults = $event->getDefaults();
			foreach ($defaults as $key => $array) {
				foreach ($array as $subkey => $row) {
					$this->_messages["$key.$subkey"] = $row['message'];
				}
			}
		}

		$models = $this->args;
		if (!$models) {
		}

		$this->hr();
		$this->out(sprintf('Generating translation strings for models: %s.', implode($models, ', ')));
		$this->out('');

		$this->_generateTranslations(false);
		foreach ($models as $model) {
			$this->_generateTranslations($model);
		}

		$this->_writeFile();
	}

/**
 * _generateTranslations
 *
 * @param mixed $modelName
 * @return void
 */
	protected function _generateTranslations($modelName) {
		$this->_strings[] = '';
		$this->_strings[] = '/**';
		if ($modelName) {
			$this->_strings[] = " * $modelName CRUD Component translations";
		} else {
			$this->_strings[] = " * Common CRUD Component translations";
		}
		$this->_strings[] = ' */';
		foreach ($this->_messages as $message) {
			$type = strpos($message, '}') ? 'model' : 'common';

			if ($type === 'model' && $modelName || $type === 'common' && !$modelName) {
				$message = String::insert($message, array('name' => $modelName), array('before' => '{', 'after' => '}'));
				$this->_strings[] = "__d('crud', '$message');";
			}
		}
	}

/**
 * _writeFile
 *
 * @return void
 */
	protected function _writeFile() {
		$path = APP . 'Config/i18n_crud.php';

		if (!file_exists($path)) {
			array_unshift($this->_strings, '<?php');
		}

		$lines = implode($this->_strings, "\n") . "\n";
		$file = new File($path, true, 0644);
		$file->append($lines);

		$this->out(str_replace('APP', '', $path) . ' updated');
		$this->hr();
	}
}
