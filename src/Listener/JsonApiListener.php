<?php
namespace Crud\Listener;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\Utility\Inflector;
use Crud\Error\Exception\CrudException;
use Crud\Event\Subject;
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
        'urlPrefix' => null, // string holding URL to prefix links in jsonapi response with
        'withJsonApiVersion' => false, // true or array/hash with additional meta information (will add top-level node `jsonapi` to the response)
        'meta' => false, // array or hash with meta information (will add top-level node `meta` to the response)
        'include' => [],
        'fieldSets' => [], // hash to limit fields shown (applicable to both `data` and `included` nodes)
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
        $this->_checkRequestMethods();
        $this->_validateConfigOptions();
        $this->_decodeIncomingJsonApiData();
    }

    /**
     * Selects an specific Crud view class to render the output
     *
     * @param \Crud\Event\Subject $subject Subject
     * @return \Cake\Network\Response
     */
    public function render(Subject $subject)
    {
        $controller = $this->_controller();
        $controller->viewBuilder()->className('Crud.JsonApi');

        // Set required viewVar with ORM AssociationCollection for the current
        // model so it can be used to generate the `relationships` nodes in the
        // response.
        //
        // Please note that we are removing associated models not found in
        // the find() result to prevent `null` relationships appearing in
        // the response.
        $tableName = $controller->name; // e.g. Countries
        $entityName = Inflector::singularize($tableName); // e.g. Country
        $table = $controller->$tableName; // table object

        if ($controller->request->action === 'index') {
            $findResult = $subject->entities;
            $firstEntity = $subject->entities->first();
        } else {
            $findResult = $subject->entity;
            $firstEntity = $subject->entity;
        }

        $associations = $table->associations();

        foreach ($associations as $association) {
            $associationKey = strtolower($association->name());
            $entityKey = $association->table();

            if (get_class($association) === 'Cake\ORM\Association\BelongsTo') {
                $entityKey = Inflector::singularize($entityKey);
            }

            if (empty($firstEntity->$entityKey)) {
                $associations->remove($associationKey);
            }
        }

        // Set required viewVar with array holding entity names of current
        // entity and all related/contained models. Used to generate/read
        // NeoMerx schemas inside the view.
        $entities = [$entityName];

        foreach ($associations as $association) {
            $entities[] = Inflector::singularize($association->name());
        }

        // Only include queryLog viewVar in debug mode
        if (Configure::read('debug')) {
            $controller->set([
                '_queryLog' => $this->_getQueryLogs()
            ]);
        }

        // Set data before rendering the view
        $controller->set([
            Inflector::tableize($controller->name) => $findResult,
            '_urlPrefix' => $this->config('urlPrefix'),
            '_withJsonApiVersion' => $this->config('withJsonApiVersion'),
            '_meta' => $this->config('meta'),
            '_entities' => $entities,
            '_associations' => $associations,
            '_include' => $this->config('include'),
            '_fieldSets' => $this->config('fieldSets'),
            '_serialize' => true,
        ]);

        return $controller->render();
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
            throw new BadRequestException("JsonApiListener requests require the $jsonApiMimeType Accept header");
        }

        if (!$this->_request()->contentType()) {
            return true;
        }

        if ($this->_request()->contentType() !== $jsonApiMimeType) {
            throw new BadRequestException("Posting data to JsonApiListener requires the $jsonApiMimeType Content-Type header");
        }

        return true;
    }

    /**
     * Transforms incoming request data in JSON API format to CakePHP format.
     *
     * @return bool
     */
    protected function _decodeIncomingJsonApiData()
    {
        $data = $this->_controller()->request->data();

        if (empty($data)) {
            return false;
        }

        $result = $data['data']['attributes'];

        // record without foreign keys
        if (!isset($data['data']['relationships'])) {
            $this->_controller()->request->data = $result;

            return true;
        }

        // record wih foreign keys
        foreach ($data['data']['relationships'] as $key => $details) {
            if (!isset($details['data'][0])) {
                $foreignKey = Inflector::singularize($details['data']['type']) . '_id';
                $foreignId = $details['data']['id'];
            } else {
                $foreignKey = Inflector::singularize($details['data'][0]['type']) . '_id';
                $foreignId = $details['data'][0]['id'];
            }

            $result[$foreignKey] = $foreignId;
        }

        $this->_controller()->request->data = $result;

        return true;
    }
}
