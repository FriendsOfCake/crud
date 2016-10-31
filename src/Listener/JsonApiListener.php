<?php
namespace Crud\Listener;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\Orm\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Crud\Error\Exception\CrudException;
use Crud\Event\Subject;
use Crud\Listener\JsonApi\DocumentValidator;
use Crud\Traits\QueryLogTrait;

/**
 * Extends Crud ApiListener to respond in JSON API format.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiListener extends ApiListener
{
    use QueryLogTrait;

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
        'meta' => false, // array or hash with meta information (will add top-level node `meta` to the response)
        'urlPrefix' => null, // string holding URL to prefix links in jsonapi response with
        'jsonOptions' => [], // array with predefined JSON constants as described at http://php.net/manual/en/json.constants.php
        'debugPrettyPrint' => true, // true to use JSON_PRETTY_PRINT for generated debug-mode response
        'debugQueryLog' => true, // adds top-level member `query` holding SQL logs in debug-mode
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

        // Accept body data posted with Content-Type `application/vnd.api+json`
        $this->_controller()->RequestHandler->config([
            'inputTypeMap' => [
                'jsonapi' => ['json_decode', true]
            ]
        ]);

        return [
            'Crud.beforeFilter' => ['callable' => [$this, 'setupLogging'], 'priority' => 1],
            'Crud.beforeHandle' => ['callable' => [$this, 'beforeHandle'], 'priority' => 10],
            'Crud.setFlash' => ['callable' => [$this, 'setFlash'], 'priority' => 5],
            'Crud.afterSave' => ['callable' => [$this, 'afterSave'], 'priority' => 90],
            'Crud.beforeRender' => ['callable' => [$this, 'respond'], 'priority' => 100],
            'Crud.beforeRedirect' => ['callable' => [$this, 'respond'], 'priority' => 100]
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
     * @return bool
     */
    public function afterSave($event)
    {
        if (!$event->subject()->success) {
            return false;
        }

        // created will be set for `add` actions, id for `edit` actions
        if (!$event->subject()->created && !$event->subject()->id) {
            return false;
        }

        $this->_insertBelongsTo($event);

        $this->_response()->header([
            'Location' => $this->_getNewResourceUrl(
                $this->_controller()->name,
                $event->subject()->entity->id
            )
        ]);

        return true;
    }

    /**
     * respond() event used to remove foreign key fields from entity/entities.
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function respond(\Cake\Event\Event $event)
    {
        $this->_removeForeignKeys($event);

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
    protected function _insertBelongsTo($event)
    {
        $entity = $event->subject()->entity;
        $table = TableRegistry::get($this->_controller()->name);
        $associations = $table->associations();

        foreach ($associations as $association) {
            if (is_a($association, '\Cake\ORM\Association\BelongsTo')) {
                $associationTable = TableRegistry::get($association->name());
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
    protected function _removeForeignKeys($event)
    {
        $table = TableRegistry::get($this->_controller()->name);
        $associations = $table->associations();

        $foreignKeys = [];
        foreach ($associations as $association) {
            if (is_a($association, '\Cake\ORM\Association\BelongsTo')) {
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
     * Creates a RESTful resource route URL rom a Router-generated URL by
     * removing last occurrence of `/view/`.
     *
     * @param string $controller Controller name
     * @param mixed $id Entity id
     * @return mixed|string
     */
    protected function _getNewResourceUrl($controller, $id)
    {
        $url = Router::url([
            'controller' => $controller,
            'action' => 'view',
            $id,
        ], true);

        $url = preg_replace('/(\/view\/(?!.*\/view\/))/', '/', $url);

        return $url;
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

        // Only set viewVar with data for the `query` node in debug mode
        if (Configure::read('debug')) {
            $controller->set([
                '_queryLogs' => $this->_getQueryLogs()
            ]);
        }

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
            '_urlPrefix' => $this->config('urlPrefix'),
            '_jsonOptions' => $this->config('jsonOptions'),
            '_debugPrettyPrint' => $this->config('debugPrettyPrint'),
            '_debugQueryLog' => $this->config('debugQueryLog'),
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
        $tableName = $this->_controller()->name; // e.g. Countries
        $entityName = Inflector::singularize($tableName); // e.g. Country
        $table = $this->_controller()->$tableName; // table object

        // Remove associations not found in the `find()` result
        $entity = $this->_getSingleEntity($subject);
        $associations = $this->_stripNonContainedAssociations($table, $entity);

        // Set data before rendering the view
        $this->_controller()->set([
            '_withJsonApiVersion' => $this->config('withJsonApiVersion'),
            '_meta' => $this->config('meta'),
            '_urlPrefix' => $this->config('urlPrefix'),
            '_jsonOptions' => $this->config('jsonOptions'),
            '_debugPrettyPrint' => $this->config('debugPrettyPrint'),
            '_debugQueryLog' => $this->config('debugQueryLog'),
            '_entities' => $this->_getEntityList($entityName, $associations),
            '_include' => $this->config('include'),
            '_fieldSets' => $this->config('fieldSets'),
            Inflector::tableize($tableName) => $this->_getFindResult($subject),
            '_associations' => $associations,
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
        if ($this->config('urlPrefix') !== null && !is_string($this->config('urlPrefix'))) {
            throw new CrudException('JsonApiListener configuration option `urlPrefix` only accepts a string');
        }

        if ($this->config('withJsonApiVersion')) {
            if (!is_bool($this->config('withJsonApiVersion')) && !is_array($this->config('withJsonApiVersion'))) {
                throw new CrudException('JsonApiListener configuration option `withJsonApiVersion` only accepts a boolean or an array');
            }
        }

        if ($this->config('meta')) {
            if (!is_array($this->config('meta'))) {
                throw new CrudException('JsonApiListener configuration option `meta` only accepts an array');
            }
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

        if (!is_bool($this->config('debugQueryLog'))) {
            throw new CrudException('JsonApiListener configuration option `debugQueryLog` only accepts a boolean');
        }
    }

    /**
     * Override ApiListener method to require JSON API Accept Type and
     * Content-Type request headers.
     *
     * @throws \Cake\Network\Exception\BadRequestException
     * @return bool
     */
    protected function _checkRequestMethods()
    {
        $jsonApiMimeType = $this->_response()->getMimeType('jsonapi');

        if (!$this->_checkRequestType('jsonapi')) {
            throw new BadRequestException("JSON API requests require the \"$jsonApiMimeType\" Accept header");
        }

        if ($this->_request()->is('put')) {
            throw new BadRequestException('JSON API does not support the PUT method, use PATCH instead');
        }

        if (!$this->_request()->contentType()) {
            return true;
        }

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
     * @param \Cake\ORM\Table $table Table
     * @param \Cake\ORM\Entity $entity Entity
     * @return \Cake\ORM\AssociationCollection
     */
    protected function _stripNonContainedAssociations($table, $entity)
    {
        $associations = $table->associations();

        foreach ($associations as $association) {
            $associationKey = strtolower($association->name());
            $entityKey = $association->table();

            if (get_class($association) === 'Cake\ORM\Association\BelongsTo') {
                $entityKey = Inflector::singularize($entityKey);
            }

            if (empty($entity->$entityKey)) {
                $associations->remove($associationKey);
            }
        }

        return $associations;
    }

    /**
     * Returns a list with entity names of both passed entity name (as string)
     * and all related/associated models. Used to read or generate NeoMerx
     * schemas inside the view.
     *
     * @param string $entityName Name of the current entity
     * @param \Cake\ORM\AssociationCollection $associations AssciationCollection
     * @return array
     */
    protected function _getEntityList($entityName, $associations)
    {
        $entities = [$entityName];

        foreach ($associations as $association) {
            $entities[] = Inflector::singularize($association->name());
        }

        return $entities;
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
        if (empty($this->_controller()->request->data())) {
            return;
        }

        // prevent false positives caused by query parameters in GET requests
        // resulting in set request data
        if (isset($this->_controller()->request->query)) {
            return;
        }

        $data = $this->_controller()->request->data();
        $validator = new DocumentValidator($data, $this->config());

        if ($this->_controller()->request->method() === 'POST') {
            $validator->validateCreateDocument();
        }

        if ($this->_controller()->request->method() === 'PATCH') {
            $validator->validateUpdateDocument();
        }

        $this->_controller()->request->data = $this->_convertJsonApiDocumentArray($data);
    }

    /**
     * Converts (already json_decoded) request data array in JSON API document
     * format to CakePHP format so it be processed as usual. Should only be
     * used with already validated data/document or things will break.
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

        // convert relationships to belongsTo foreign key `_id` fields
        foreach ($document['data']['relationships'] as $key => $details) {
            if (!isset($details['data'][0])) {
                $foreignKey = Inflector::singularize($details['data']['type']) . '_id';
                $foreignId = $details['data']['id'];
            } else {
                $foreignKey = Inflector::singularize($details['data'][0]['type']) . '_id';
                $foreignId = $details['data'][0]['id'];
            }

            $result[$foreignKey] = $foreignId;
        }

        return $result;
    }
}
