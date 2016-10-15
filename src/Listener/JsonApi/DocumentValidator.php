<?php
namespace Crud\Listener\JsonApi;

use Cake\ORM\Entity;
use Cake\Validation\Validation;
use Crud\Core\Object;
use Crud\Error\Exception\CrudException;
use Crud\Error\Exception\ValidationException;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Exceptions\ErrorCollection;
use StdClass;

/**
 * Validates incoming JSON API documents against the specifications for
 * CRUD actions described at http://jsonapi.org/format/#crud.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class DocumentValidator extends Object
{
    /**
     * RequestHandler decoded JSON API document array.
     *
     * @var array $_document
     */
    protected $_document;

    /**
     * Name of the document based on current controller action (e.g.
     * JSON API CreateDocument) so we can provide user friendly feedback
     * in the validation error messages.
     *
     * @var string $_action E.g. JSON API CreateDocument
     */
    protected $_documentName;

    /**
     * @var \Neomerx\JsonApi\Exceptions\ErrorCollection
     */
    protected $_errorCollection;

    /**
     * var array JsonApiListener config() options
     */
    protected $_config;

    /**
     * Constructor
     *
     * @param array $documentArray Decoded JSON API document
     * @param array $listenerConfig JsonApiListener config() options
     * @return void
     */
    public function __construct(array $documentArray, array $listenerConfig)
    {
        $this->_document = $documentArray;

        $this->_config = $listenerConfig;

        $this->_errorCollection = new ErrorCollection();
    }

    /**
     * Validates a JSON API document used for Creating resources against the
     * requirements described at http://jsonapi.org/format/#crud-creating.
     *
     * @return void
     */
    public function validateCreateDocument()
    {
        $this->_documentName = "JSON API CreateDocument";

        $this->_mustHaveTopLevelMemberNamedData();

        $this->_mustHaveValidStringMember('data.type', 'http://jsonapi.org/format/#crud-creating');

        if ($this->_hasProperty('data.id')) {
            $this->_mustBeUuid('data.id');
        }

        if ($this->_errorCollection->count() !== 0) {
            $this->_throwValidationError();
        }
    }

    /**
     * Document MUST have the top-level member `data`. If not, throw the
     * correct custom validation error with a pointer to '' as described at
     * http://jsonapi.org/examples/#error-objects-source-usage.
     *
     * @return void
     */
    protected function _mustHaveTopLevelMemberNamedData()
    {
        if ($this->_hasProperty('data') === false) {
            $this->_errorCollection->add(new Error(
                $idx = null,
                $aboutLink = $this->_getAboutLInk('http://jsonapi.org/format/#document-top-level'),
                $status = null,
                $code = null,
                $title = null,
                $detail = $this->_documentName . " does not contain required top-level member 'data'",
                $source = [
                    'pointer' => ''
                ],
                $meta = null
            ));

            $this->_throwValidationError();
        }
    }

    /**
     * Member MUST be present and MUST be a string.
     *
     * @param string $path To and including the property to check
     * @param string $url Optional aboutLink
     * @return void|bool
     */
    protected function _mustHaveValidStringMember($path, $url = null)
    {
        $path = $this->_getPathObject($path);

        if ($this->_hasProperty($path->dotted) === false) {
            $this->_errorCollection->addDataError(
                $title = '_required',
                $detail = $this->_documentName . " misses required member '$path->key' for resource object '$path->toKey'",
                $status = null,
                $idx = null,
                $aboutLink = new Link($url)
            );

            return false;
        }

        $value = $this->_getProperty($path->dotted);

        if (is_string($value)) {
            return true;
        }

        $this->_errorCollection->addDataTypeError(
            $title = '_notString',
            $details = $this->_documentName . " member '$path->key' for resource object '$path->toKey' MUST be a string",
            $status = null,
            $idx = null,
            $aboutLink = new Link('http://jsonapi.org/format/#document-resource-object-identification')
        );
    }

    /**
     * Member value MUST be a valid client-generated UUID.
     *
     * @param string $path To and including the property to check
     * @return bool
     */
    protected function _mustBeUuid($path)
    {
        $path = $this->_getPathObject($path);

        if (!$this->_hasProperty($path->dotted)) {
            throw new CrudException("Document member '$path->dotted' does not exist");
        }

        $id = $this->_getProperty($path->dotted);

        if (Validation::uuid($id)) {
            return true;
        }

        $this->_errorCollection->addDataIdError(
            $title = '_notUuid',
            $details = $this->_documentName . " member '$path->key' for resource object '$path->toKey' MUST be a valid UUID",
            $status = null,
            $idx = null,
            $aboutLink = new Link('http://jsonapi.org/format/#crud-creating-client-ids')
        );
    }

    /**
     * Checks if document contains a dot-separated property (even when value
     * is `false` or `null`).
     *
     * @param string $path To and including the property to check
     * @return mixed|bool
     */
    protected function _hasProperty($path)
    {
        $current = $this->_document;
        $parts = strtok($path, '.');

        while ($parts !== false) {
            if (!is_array($current)) {
                return false;
            }
            if (!array_key_exists($parts, $current)) {
                return false;
            }
            $current = $current[$parts];
            $parts = strtok('.');
        }

        return true;
    }

    /**
     * Retrieves a dot-separated property from the document.
     *
     * @param string $path To and and including the property to check
     * @throws \Crud\Error\Exception\CrudException
     * @return mixed
     */
    protected function _getProperty($path)
    {
        $current = $this->_document;

        $pathClone = $path;
        $parts = strtok($pathClone, '.');

        while ($parts !== false) {
            if (!is_array($current)) {
                return $current;
            }
            if (!array_key_exists($parts, $current)) {
                throw new CrudException("Error retrieving a value for non-existing JSON API document property '$path'");
            }
            $current = $current[$parts];
            $parts = strtok('.');
        }

        return $current;
    }

    /**
     * Helper method for creating object with consistent path strings.
     *
     * @param string $path Dot separated path
     * @return \StdClass
     */
    protected function _getPathObject($path)
    {
        $obj = new StdClass();
        $obj->dotted = $path;

        $parts = explode('.', $path);

        if (count($parts) === 1) {
            $obj->toKey = null;
            $obj->key = $path;

            return $obj;
        }

        $key = end($parts);
        array_pop($parts);
        $obj->toKey = implode('.', $parts);
        $obj->key = $key;

        return $obj;
    }

    /**
     * Helper method to display aboutLink only if enabled in Listener config
     *
     * @param string $url URL
     * @return \Neomerx\JsonApi\Document\Link
     */
    protected function _getAboutLInk($url)
    {
        if ($this->_config['docValidatorAboutLinks'] === false) {
            return null;
        }

        return new Link($url);
    }

    /**
     * Helper method to make the ErrorCollection object available inside the
     * JsonApiExceptionRenderer validation() method by cloaking it as a
     * default CakePHP validation error.
     *
     * @return void
     */
    protected function _throwValidationError()
    {
        $entity = new Entity();

        $entity->errors('CrudJsonApiListener', [
            'NeoMerxErrorCollection' => $this->_errorCollection
        ]);

        throw new ValidationException($entity);
    }
}
