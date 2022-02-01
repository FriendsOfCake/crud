<?php
declare(strict_types=1);

namespace Crud\Error\Exception;

use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;
use Throwable;

/**
 * Exception containing validation errors from the model. Useful for API
 * responses where you need an error code in response
 */
class ValidationException extends BadRequestException
{
    /**
     * List of validation errors that occurred in the model
     *
     * @var array
     */
    protected $_validationErrors = [];

    /**
     * How many validation errors are there?
     *
     * @var int
     */
    protected $_validationErrorCount = 0;

    /**
     * Constructor
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param int $code code to report to client
     * @param \Throwable|null $previous The previous exception.
     */
    public function __construct(EntityInterface $entity, int $code = 422, ?Throwable $previous = null)
    {
        $this->_validationErrors = array_filter($entity->getErrors());
        $flat = Hash::flatten($this->_validationErrors);

        $errorCount = $this->_validationErrorCount = count($flat);
        $this->message = __dn(
            'crud',
            'A validation error occurred',
            '{0} validation errors occurred',
            $errorCount,
            [$errorCount]
        );

        parent::__construct($this->message, $code, $previous);
    }

    /**
     * Returns the list of validation errors
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->_validationErrors;
    }

    /**
     * How many validation errors are there?
     *
     * @return int
     */
    public function getValidationErrorCount(): int
    {
        return $this->_validationErrorCount;
    }
}
