<?php

namespace Crud\Error;

use Cake\Error\BaseException;
use Cake\ORM\Entity;
use Cake\Utility\Hash;

/**
 * Exception containing validation errors from the model. Useful for API
 * responses where you need an error code in response
 *
 */
class CrudValidationException extends BaseException {

/**
 * List of validation errors that occurred in the model
 *
 * @var array
 */
	protected $_validationErrors = [];

/**
 * How many validation errors are there?
 *
 * @var integer
 */
	protected $_validationErrorCount = 0;

/**
 * Constructor
 *
 * @param array $error list of validation errors
 * @param integer $code code to report to client
 * @return void
 */
	public function __construct(Entity $entity, $code = 412) {
		$this->_validationErrors = array_filter((array)$entity->errors());
		$flat = Hash::flatten($this->_validationErrors);

		$errorCount = $this->_validationErrorCount = count($flat);
		$this->message = __dn('crud', 'A validation error occurred', '%d validation errors occurred', $errorCount, [$errorCount]);

		parent::__construct($this->message, $code);
	}

/**
 * Returns the list of validation errors
 *
 * @return array
 */
	public function getValidationErrors() {
		return $this->_validationErrors;
	}

/**
 * How many validation errors are there?
 *
 * @return integer
 */
	public function getValidationErrorCount() {
		return $this->_validationErrorCount;
	}

}
