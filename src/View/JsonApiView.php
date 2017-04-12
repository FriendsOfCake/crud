<?php
namespace Crud\View;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\RepositoryInterface;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\View\View;
use Crud\Error\Exception\CrudException;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;

class JsonApiView extends View
{
    /**
     * Constructor
     *
     * @param \Cake\Network\Request $request Request
     * @param \Cake\Network\Response $response Response
     * @param \Cake\Event\EventManager $eventManager EventManager
     * @param array $viewOptions An array of view options
     */
    public function __construct(
        Request $request = null,
        Response $response = null,
        EventManager $eventManager = null,
        array $viewOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $viewOptions);

        if ($response && $response instanceof Response) {
            $response->type('jsonapi');
        }
    }

    /**
     * Returns an array of special viewVars (names starting with an underscore).
     *
     * We need to dynamically generate this array to prevent special vars
     * from the app or other plugins being passed to NeoMerx for processing
     * as data (and thus effectively breaking this view).
     *
     * @return array
     */
    protected function _getSpecialVars()
    {
        $result = [];

        $viewVarKeys = array_keys($this->viewVars);
        foreach ($viewVarKeys as $viewVarKey) {
            if ($viewVarKey[0] === '_') {
                $result[] = $viewVarKey;
            }
        }

        return $result;
    }

    /**
     * Renders one of the three supported JSON API responses:
     *
     * - with body containing an entity based resource (data)
     * - with empty body
     * - with body containing only the meta node
     *
     * @param string|null $view Name of view file to use
     * @param string|null $layout Layout to use.
     * @return string
     */
    public function render($view = null, $layout = null)
    {
        if (isset($this->viewVars['_repositories'])) {
            $json = $this->_encodeWithSchemas();
        } else {
            $json = $this->_encodeWithoutSchemas();
        }

        // Add query logs node if ApiQueryLogListener is loaded
        if (Configure::read('debug') && isset($this->viewVars['queryLog'])) {
            $json = json_decode($json, true);
            $json['query'] = $this->viewVars['queryLog'];
            $json = json_encode($json, $this->_jsonOptions());
        }

        return $json;
    }

    /**
     * Generates a JSON API string without resource(s).
     *
     * @return void|string
     */
    protected function _encodeWithoutSchemas()
    {
        if (empty($this->viewVars['_meta'])) {
            return;
        }

        $encoder = Encoder::instance(
            [],
            new EncoderOptions(
                $this->_jsonOptions()
            )
        );

        return $encoder->encodeMeta($this->viewVars['_meta']);
    }

    /**
     * Generates a JSON API string with resource(s).
     *
     * @return string
     */
    protected function _encodeWithSchemas()
    {
        $schemas = $this->_entitiesToNeoMerxSchema($this->viewVars['_repositories']);

        // Please note that a third NeoMerx EncoderOptions argument `depth`
        // exists but has not been implemented in this plugin.
        $encoder = Encoder::instance(
            $schemas,
            new EncoderOptions(
                $this->_jsonOptions()
            )
        );

        $serialize = null;
        if (isset($this->viewVars['_serialize'])) {
            $serialize = $this->viewVars['_serialize'];
        }

        if (isset($this->viewVars['_serialize']) && $this->viewVars['_serialize'] !== false) {
            $serialize = $this->_getDataToSerializeFromViewVars($this->viewVars['_serialize']);
        }

        // By default the listener will automatically add all associated data
        // (as produced by containable and thus present in the entity) to the
        // '_include' viewVar which is used to produce the top-level `included`
        // node UNLESS user specified an array with associations using the
        // listener config option `include`.
        //
        // Please be aware that `include` will take precedence over the
        //`getIncludePaths()` method that MIGHT exist in custom NeoMerx schemas.
        //
        // Lastly, listener config option `fieldSets` may be used to limit
        // the fields shown in the result.
        $include = $this->viewVars['_include'];
        $fieldSets = $this->viewVars['_fieldSets'];

        $parameters = new EncodingParameters(
            $include,
            $fieldSets
        );

        // Add optional top-level `version` node to the response if enabled
        // by user using listener config option.
        if ($this->viewVars['_withJsonApiVersion']) {
            if (is_array($this->viewVars['_withJsonApiVersion'])) {
                $encoder->withJsonApiVersion($this->viewVars['_withJsonApiVersion']);
            } else {
                $encoder->withJsonApiVersion();
            }
        }

        // Add top-level `links` node with pagination information (requires
        // ApiPaginationListener which will have set/filled viewVar).
        if (isset($this->viewVars['_pagination'])) {
            $pagination = $this->viewVars['_pagination'];

            $encoder->withLinks($this->_getPaginationLinks($pagination));

            // Additional pagination information has to be in top-level node `meta`
            $this->viewVars['_meta']['record_count'] = $pagination['record_count'];
            $this->viewVars['_meta']['page_count'] = $pagination['page_count'];
            $this->viewVars['_meta']['page_limit'] = $pagination['page_limit'];
        }

        // Add optional top-level `meta` node to the response if enabled by
        // user using listener config option.
        if (!empty($this->viewVars['_meta'])) {
            if (empty($serialize)) {
                return $encoder->encodeMeta($this->viewVars['_meta']);
            } else {
                $encoder->withMeta($this->viewVars['_meta']);
            }
        }

        return $encoder->encodeData($serialize, $parameters);
    }

    /**
     * Maps each entity to the first schema match in this order:
     * 1. custom entity schema
     * 2. custom dynamic schema
     * 3. Crud's dynamic schema
     *
     * @param RepositoryInterface[] $repositories List holding repositories used to map entities to schema classes
     * @throws \Crud\Error\Exception\CrudException
     * @return array A list with Entity class names as key holding NeoMerx Closure object
     */
    protected function _entitiesToNeoMerxSchema(array $repositories)
    {
        $schemas = [];
        foreach ($repositories as $repositoryName => $repository) {
            if (isset($schemas[$repository->entityClass()])) {
                continue;
            }

            $entityClass = $repository->entityClass();

            // Turn full class name back into plugin split format
            // Not including /Entity in the type makes sure its compatible with other types
            $entityName = App::shortName($entityClass, 'Model');

            // Take plugin name and entity name off
            list($pluginName, $entityName) = pluginSplit($entityName, true);

            // Find the first namespace separator to take everything after the entity type.
            $firstNamespaceSeparator = strpos($entityName, '/');
            if ($firstNamespaceSeparator === false) {
                throw new CrudException('Invalid entity name specified');
            }
            $entityName = substr($entityName, $firstNamespaceSeparator + 1);

            $entityName = $pluginName . $entityName;

            // If user created a custom entity schema... use it
            $schemaClass = App::className($entityName, 'Schema\JsonApi', 'Schema');

            // If user created a custom dynamic schema... use it
            if (!$schemaClass) {
                $schemaClass = App::className('DynamicEntity', 'Schema\JsonApi', 'Schema');
            }

            // Otherwise use the dynamic schema provided by Crud
            if (!$schemaClass) {
                $schemaClass = App::className('Crud.DynamicEntity', 'Schema\JsonApi', 'Schema');
            }

            // Uses NeoMerx createSchemaFromClosure()` to generate Closure
            // object with schema information.
            $schema = function ($factory) use ($schemaClass, $repository) {
                return new $schemaClass($factory, $this, $repository);
            };

            // Add generated schema to the collection before processing next
            $schemas[$repository->entityClass()] = $schema;
        }

        return $schemas;
    }

    /**
     * Returns an array with NeoMerx Link objects to be used for pagination.
     *
     * @param array $pagination ApiPaginationListener pagination response
     * @return array
     */
    protected function _getPaginationLinks($pagination)
    {
        $links = [
            Link::SELF => null,
            Link::FIRST => null,
            Link::LAST => null,
            Link::PREV => null,
            Link::NEXT => null,
        ];

        if (isset($pagination['self'])) {
            $links[Link::SELF] = new Link($pagination['self'], null, true);
        }

        if (isset($pagination['first'])) {
            $links[Link::FIRST] = new Link($pagination['first'], null, true);
        }

        if (isset($pagination['last'])) {
            $links[Link::LAST] = new Link($pagination['last'], null, true);
        }

        if (isset($pagination['prev'])) {
            $links[Link::PREV] = new Link($pagination['prev'], null, true);
        }

        if (isset($pagination['next'])) {
            $links[Link::NEXT] = new Link($pagination['next'], null, true);
        }

        return $links;
    }

    /**
     * Returns data to be serialized.
     *
     * @param array|string|bool $serialize The name(s) of the view variable(s) that
     *   need(s) to be serialized. If true all available view variables will be used.
     * @return mixed The data to serialize.
     */
    protected function _getDataToSerializeFromViewVars($serialize = true)
    {
        if (is_object($serialize)) {
            throw new CrudException('Assigning an object to JsonApiListener "_serialize" is deprecated, assign the object to its own variable and assign "_serialize" = true instead.');
        }

        if ($serialize === true) {
            $data = array_diff_key(
                $this->viewVars,
                array_flip($this->_getSpecialVars())
            );

            if (empty($data)) {
                return null;
            }

            return current($data);
        }

        if (is_array($serialize)) {
            $serialize = current($serialize);
        }

        return isset($this->viewVars[$serialize]) ? $this->viewVars[$serialize] : null;
    }

    /**
     * Returns an integer flag holding any combination of php predefined json
     * option constants as found at http://php.net/manual/en/json.constants.php.
     *
     * @return int Flag holding json options
     */
    protected function _jsonOptions()
    {
        $jsonOptions = 0;

        if (!empty($this->viewVars['_jsonOptions'])) {
            foreach ($this->viewVars['_jsonOptions'] as $jsonOption) {
                $jsonOptions = $jsonOptions | $jsonOption;
            }
            $jsonOptions = $jsonOptions | $jsonOption;
        }

        if (Configure::read('debug') === false) {
            return $jsonOptions;
        }

        if ($this->viewVars['_debugPrettyPrint']) {
            $jsonOptions = $jsonOptions | JSON_PRETTY_PRINT;
        }

        return $jsonOptions;
    }
}
