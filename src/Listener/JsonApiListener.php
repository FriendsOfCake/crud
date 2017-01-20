<?php
namespace Crud\Listener;

use Cake\Datasource\RepositoryInterface;
use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\Association;
use Cake\Utility\Inflector;
use Crud\Error\Exception\CrudException;
use Crud\Event\Subject;
use Crud\Listener\JsonApi\DocumentValidator;
use Crud\Traits\JsonApiTrait;

/**
 * Extends Crud ApiListener to respond in JSON API format.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiListener extends ApiListener
{

    use JsonApiTrait;

    /**
     * Required composer package with Crud supported version
     *
     * @var string
     */
    protected $neomerxPackage = 'neomerx/json-api:^0.8.10';

    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [
        'detectors' => [
            'jsonapi' => ['ext' => 'json', 'accepts' => 'application/vnd.api+json'],
        ],
        'exception' => [
            'type' => 'default',
            'class' => 'Cake\Network\Exception\BadRequestException',
            'message' => 'Unknown error',
            'code' => 0,
        ],
        'exceptionRenderer' => 'Crud\Error\JsonApiExceptionRenderer',
        'setFlash' => false,
        'withJsonApiVersion' => false, // true or array/hash with additional meta information (will add top-level member `jsonapi` to the response)
        'meta' => [], // array or hash with meta information (will add top-level node `meta` to the response)
        'absoluteLinks' => false, // false to generate relative links, true will generate fully qualified URL prefixed with http://domain.name
        'jsonApiBelongsToLinks' => false, // false to generate JSONAPI links (requires custom Route, included)
        'jsonOptions' => [], // array with predefined JSON constants as described at http://php.net/manual/en/json.constants.php
        'debugPrettyPrint' => true, // true to use JSON_PRETTY_PRINT for generated debug-mode response
        'include' => [],
        'fieldSets' => [], // hash to limit fields shown (can be used for both `data` and `included` members)
        'docValidatorAboutLinks' => false, // true to show links to JSON API specification clarifying the document validation error
    ];

    /**
     * Returns a list of all events that will fire in the controller during its lifecycle.
     * You can override this function to add you own listener callbacks
     *
     * We attach at priority 10 so normal bound events can run before us
     *
     * @return array
     */
    public function implementedEvents()
    {
        $this->setupDetectors();

        // make sure the listener does absolutely nothing unless
        // the application/vnd.api+json Accept header is used.
        if (!$this->_checkRequestType('jsonapi')) {
            return null;
        }

        // accept body data posted with Content-Type `application/vnd.api+json`
        $this->_controller()->RequestHandler->config([
            'inputTypeMap' => [
                'jsonapi' => ['json_decode', true]
            ]
        ]);

        return [
            'Crud.beforeHandle' => ['callable' => [$this, 'beforeHandle'], 'priority' => 10],
            'Crud.setFlash' => ['callable' => [$this, 'setFlash'], 'priority' => 5],
            'Crud.afterSave' => ['callable' => [$this, 'afterSave'], 'priority' => 90],
            'Crud.afterDelete' => ['callable' => [$this, 'afterDelete'], 'priority' => 90],
            'Crud.beforeRender' => ['callable' => [$this, 'respond'], 'priority' => 100],
            'Crud.beforeRedirect' => ['callable' => [$this, 'beforeRedirect'], 'priority' => 100],
        ];
    }

    /**
     * beforeHandle
     *
     * Called before the crud action is executed.
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeHandle(Event $event)
    {
        $this->_checkPackageDependencies();
        $this->_checkRequestMethods();
        $this->_validateConfigOptions();
        $this->_checkRequestData();
    }

    /**
     * afterSave() event used to respond with `Location` header pointing to
     * the newly created resources. Only applied to `add` actions as described
     * at http://jsonapi.org/format/#crud-creating-responses-201.
     *
     * @param \Cake\Event\Event $event Event
     * @return false|null
     */
    public function afterSave($event)
    {
        if (!$event->subject()->success) {
            return false;
        }

        // `created` will be set for add actions, `id` for edit actions
        if (!$event->subject()->created && !$event->subject()->id) {
            return false;
        }

        $this->_insertBelongsToDataIntoEventFindResult($event);

        $this->render($event->subject);
    }

    /**
     * afterDelete() event used to respond with 402 code and empty body.
     *
     * Please note that the JSON API spec allows for a 200 response with
     * only meta node after a successful delete as well but this has not
     * been implemented here yet. http://jsonapi.org/format/#crud-deleting
     *
     * @param \Cake\Event\Event $event Event
     * @return false|null
     */
    public function afterDelete(Event $event)
    {
        if (!$event->subject()->success) {
            return false;
        }

        $this->_controller()->response->statusCode(204);
    }

    /**
     * beforeRedirect() event used to stop the event and thus redirection.
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeRedirect(Event $event)
    {
        $event->stopPropagation();
    }

    /**
     * respond() event.
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function respond(Event $event)
    {
        $this->_removeBelongsToForeignKeysFromEventData($event);

        parent::respond($event);
    }

    /**
     * Adds belongsTo data to the find() result so the 201 success response
     * is able to render the jsonapi `relationships` member.
     *
     * Please note that we are deliberately NOT creating a new find query as
     * this would not respect non-accessible fields.
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    protected function _insertBelongsToDataIntoEventFindResult($event)
    {
        $entity = $event->subject()->entity;
        $repository = $this->_controller()->loadModel();
        $associations = $repository->associations();

        foreach ($associations as $association) {
            if ($association->type() === Association::MANY_TO_ONE) {
                $associationTable = $association->target();
                $foreignKey = $association->foreignKey();

                $result = $associationTable
                    ->find()
                    ->select(['id'])
                    ->where([$association->name() . '.id' => $entity->$foreignKey])
                    ->first();

                $key = Inflector::tableize($association->name());
                $key = Inflector::singularize($key);

                $entity->$key = $result;
            }
        }

        $event->subject()->entity = $entity;
    }

    /**
     * Removes all belongsTo `_id`  fields from the entity or entities so
     * they don't show up as jsonapi attributes in the response as described
     * at http://jsonapi.org/format/#document-resource-object-attributes.
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    protected function _removeBelongsToForeignKeysFromEventData($event)
    {
        $repository = $this->_controller()->loadModel();
        $associations = $repository->associations();

        $foreignKeys = [];
        foreach ($associations as $association) {
            if ($association->type() === Association::MANY_TO_ONE) {
                $foreignKeys[] = $association->foreignKey();
            }
        }

        // remove from single entity
        if (isset($event->subject()->entity)) {
            foreach ($foreignKeys as $foreignKey) {
                $event->subject()->entity->unsetProperty($foreignKey);
            }

            return;
        }

        // remove from collection
        foreach ($event->subject()->entities as $key => $entity) {
            foreach ($foreignKeys as $foreignKey) {
                $event->subject()->entities->current()->unsetProperty($foreignKey);
            }
        }
    }

    /**
     * Set required viewVars before rendering the JsonApiView.
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return \Cake\Network\Response
     */
    public function render(Subject $subject)
    {
        $controller = $this->_controller();
        $controller->viewBuilder()->className('Crud.JsonApi');

        // render a JSON API response with resource(s) if data is found
        if (isset($subject->entity) || isset($subject->entities)) {
            return $this->_renderWithResources($subject);
        }

        return $this->_renderWithoutResources();
    }

    /**
     * Renders a resource-less JSON API response.
     *
     * @return \Cake\Network\Response
     */
    protected function _renderWithoutResources()
    {
        $this->_controller()->set([
            '_withJsonApiVersion' => $this->config('withJsonApiVersion'),
            '_meta' => $this->config('meta'),
            '_absoluteLinks' => $this->config('absoluteLinks'),
            '_jsonApiBelongsToLinks' => $this->config('jsonApiBelongsToLinks'),
            '_jsonOptions' => $this->config('jsonOptions'),
            '_debugPrettyPrint' => $this->config('debugPrettyPrint'),
            '_serialize' => true,
        ]);

        return $this->_controller()->render();
    }

    /**
     * Renders a JSON API response with top-level data node holding resource(s).
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return \Cake\Network\Response
     */
    protected function _renderWithResources($subject)
    {
        $repository = $this->_controller()->loadModel(); // Default model class

        // Remove associations not found in the `find()` result
        $entity = $this->_getSingleEntity($subject);
        $strippedAssociations = $this->_stripNonContainedAssociations($repository, $entity);

        // Set data before rendering the view
        $this->_controller()->set([
            '_withJsonApiVersion' => $this->config('withJsonApiVersion'),
            '_meta' => $this->config('meta'),
            '_absoluteLinks' => $this->config('absoluteLinks'),
            '_jsonApiBelongsToLinks' => $this->config('jsonApiBelongsToLinks'),
            '_jsonOptions' => $this->config('jsonOptions'),
            '_debugPrettyPrint' => $this->config('debugPrettyPrint'),
            '_repositories' => $this->_getRepositoryList($repository, $strippedAssociations),
            '_include' => $this->_getIncludeList($strippedAssociations),
            '_fieldSets' => $this->config('fieldSets'),
            Inflector::tableize($repository->alias()) => $this->_getFindResult($subject),
            '_associations' => $strippedAssociations,
            '_serialize' => true,
        ]);

        return $this->_controller()->render();
    }

    /**
     * Make sure the neomerx/json-api composer package is installed
     *
     * @throws \Crud\Error\Exception\CrudException
     * @return void
     */
    protected function _checkPackageDependencies()
    {
        if (!class_exists('\Neomerx\JsonApi\Encoder\Encoder')) {
            throw new CrudException('JsonApiListener requires composer installing ' . $this->neomerxPackage);
        }
    }

    /**
     * Make sure all configuration options are valid.
     *
     * @throws \Crud\Error\Exception\CrudException
     * @return void
     */
    protected function _validateConfigOptions()
    {
        if ($this->config('withJsonApiVersion')) {
            if (!is_bool($this->config('withJsonApiVersion')) && !is_array($this->config('withJsonApiVersion'))) {
                throw new CrudException('JsonApiListener configuration option `withJsonApiVersion` only accepts a boolean or an array');
            }
        }

        if (!is_array($this->config('meta'))) {
            throw new CrudException('JsonApiListener configuration option `meta` only accepts an array');
        }

        if (!is_bool($this->config('absoluteLinks'))) {
            throw new CrudException('JsonApiListener configuration option `absoluteLinks` only accepts a boolean');
        }

        if (!is_bool($this->config('jsonApiBelongsToLinks'))) {
            throw new CrudException('JsonApiListener configuration option `jsonApiBelongsToLinks` only accepts a boolean');
        }

        if (!is_array($this->config('include'))) {
            throw new CrudException('JsonApiListener configuration option `include` only accepts an array');
        }

        if (!is_array($this->config('fieldSets'))) {
            throw new CrudException('JsonApiListener configuration option `fieldSets` only accepts an array');
        }

        if (!is_array($this->config('jsonOptions'))) {
            throw new CrudException('JsonApiListener configuration option `jsonOptions` only accepts an array');
        }

        if (!is_bool($this->config('debugPrettyPrint'))) {
            throw new CrudException('JsonApiListener configuration option `debugPrettyPrint` only accepts a boolean');
        }
    }

    /**
     * Override ApiListener method to enforce required JSON API request methods.
     *
     * @throws \Cake\Network\Exception\BadRequestException
     * @return bool
     */
    protected function _checkRequestMethods()
    {
        if ($this->_request()->is('put')) {
            throw new BadRequestException('JSON API does not support the PUT method, use PATCH instead');
        }

        if (!$this->_request()->contentType()) {
            return true;
        }

        $jsonApiMimeType = $this->_response()->getMimeType('jsonapi');

        if ($this->_request()->contentType() !== $jsonApiMimeType) {
            throw new BadRequestException("JSON API requests with data require the \"$jsonApiMimeType\" Content-Type header");
        }

        return true;
    }

    /**
     * Helper function to easily retrieve `find()` result from Crud subject
     * regardless of current action.
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return mixed Single Entity or ORM\ResultSet
     */
    protected function _getFindResult($subject)
    {
        if (!empty($subject->entities)) {
            return $subject->entities;
        }

        return $subject->entity;
    }

    /**
     * Helper function to easily retrieve a single entity from Crud subject
     * find result regardless of current action.
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return \Cake\ORM\Entity
     */
    protected function _getSingleEntity($subject)
    {
        if (!empty($subject->entities)) {
            return $subject->entities->first();
        }

        return $subject->entity;
    }

    /**
     * Removes all associated models not detected (as the result of a contain
     * query) in the find result from the entity's AssociationCollection to
     * prevent `null` entries appearing in the json api `relationships` node.
     *
     * @param \Cake\Datasource\RepositoryInterface $repository Repository
     * @param \Cake\ORM\Entity $entity Entity
     * @return \Cake\ORM\AssociationCollection
     */
    protected function _stripNonContainedAssociations($repository, $entity)
    {
        $associations = $repository->associations();

        foreach ($associations as $association) {
            $associationKey = strtolower($association->name());
            $entityKey = $association->property();

            if (empty($entity->$entityKey)) {
                $associations->remove($associationKey);
            }
        }

        return $associations;
    }

    /**
     * Get a list of all repositories indexed by their registry alias.
     *
     * @param RepositoryInterface $repository Current repository
     * @param Association[] $associations Associations to get repository from
     * @return array Used repositories indexed by registry alias
     * @internal
     */
    protected function _getRepositoryList(RepositoryInterface $repository, $associations)
    {
        $repositories = [
            $repository->registryAlias() => $repository
        ];

        foreach ($associations as $association) {
            $registryAlias = $association->target()->registryAlias();

            $repositories[$registryAlias] = $association->target();
        }

        return $repositories;
    }

    /**
     * Generates a list for with all associated data (as produced by Containable
     * and thus) present in the entity to be used for filling the top-level
     * `included` node in the json response UNLESS user has specified listener
     * config option 'include'.
     *
     * @param \Cake\ORM\AssociationCollection $associations AssociationCollection
     * @return array
     */
    protected function _getIncludeList($associations)
    {
        if (!empty($this->config('include'))) {
            return $this->config('include');
        }

        $result = [];
        foreach ($associations as $association) {
            if ($association->type() === Association::MANY_TO_ONE) {
                $include = Inflector::tableize($association->name());
                $include = Inflector::singularize($include);

                $result[] = $include;
                continue;
            }

            // hasMany
            $result[] = Inflector::tableize($association->name());
        }

        return $result;
    }

    /**
     * Checks if data was posted to the Listener. If so then checks if the
     * array (already converted from json) matches the expected JSON API
     * structure for resources and if so, converts that array to CakePHP
     * compatible format so it can be processed as usual from there.
     *
     * @return void
     */
    protected function _checkRequestData()
    {
        $requestMethod = $this->_controller()->request->method();

        if ($requestMethod !== 'POST' && $requestMethod !== 'PATCH') {
            return;
        }

        $requestData = $this->_controller()->request->data();

        if (empty($requestData)) {
            return;
        }

        $validator = new DocumentValidator($requestData, $this->config());

        if ($requestMethod === 'POST') {
            $validator->validateCreateDocument();
        }

        if ($requestMethod === 'PATCH') {
            $validator->validateUpdateDocument();
        }

        $this->_controller()->request->data = $this->_convertJsonApiDocumentArray($requestData);
    }

    /**
     * Converts (already json_decoded) request data array in JSON API document
     * format to CakePHP format so it be processed as usual. Should only be
     * used with already validated data/document or things will break.
     *
     * Please note that decoding hasMany relationships has not yet been implemented.
     *
     * @param array $document Request data document array
     * @return bool
     */
    protected function _convertJsonApiDocumentArray($document)
    {
        $result = [];

        // convert primary resource
        if (array_key_exists('id', $document['data'])) {
            $result['id'] = $document['data']['id'];
        };

        if (array_key_exists('attributes', $document['data'])) {
            $result = array_merge_recursive($result, $document['data']['attributes']);
        };

        if (!array_key_exists('relationships', $document['data'])) {
            return $result;
        }

        foreach ($document['data']['relationships'] as $key => $details) {
            // skip hasMany relationships for now
            if (isset($details['data'][0])) {
                continue;
            }

            // allow empty/null data node as per the JSON API specification
            if (empty($details['data'])) {
                continue;
            }

            // convert belongsTo to CakePHP `foreign_id` format
            $foreignKey = Inflector::singularize($details['data']['type']) . '_id';
            $foreignId = $details['data']['id'];
            $result[$foreignKey] = $foreignId;
        }

        return $result;
    }
}
