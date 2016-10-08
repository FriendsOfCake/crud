<?php
namespace Crud\View;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Utility\Hash;
use Cake\View\View;
use Crud\Error\Exception\CrudException;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;

class JsonApiView extends View
{
    /**
     * List of special view vars. See Crud JsonApiListener's default config
     * for a full list with explanations.
     *
     * Please note that ALL viewVars found in the view MINUS the ones defined
     * here will be passed to NeoMerx as $data.
     *
     * @var array
     */
    protected $_specialVars = [
        '_urlPrefix',
        '_withJsonApiVersion',
        '_entities',
        '_include',
        '_fieldSets',
        '_links',
        '_meta',
        '_serialize',
        '_jsonOptions',
        '_debugPrettyPrint',
        '_debugQueryLog',
        '_jsonp',
    ];

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
     * Serialize view vars.
     *
     * @param string|null $view Name of view file to use
     * @param string|null $layout Layout to use.
     * @throws \Crud\Error\Exception\CrudException
     * @return string NeoMerx generated JSON API
     */
    public function render($view = null, $layout = null)
    {
        if (empty($this->viewVars['_entities'])) {
            throw new CrudException('JsonApiListener required viewVar \'_entities\' is not set');
        }

        $schemas = $this->_entitiesToNeoMerxSchema($this->viewVars['_entities']);

         $serialize = null;
        if (isset($this->viewVars['_serialize'])) {
            $serialize = $this->viewVars['_serialize'];
        }

        if (isset($this->viewVars['_serialize']) && $this->viewVars['_serialize'] !== false) {
            $serialize = $this->_getDataToSerializeFromViewVars($this->viewVars['_serialize']);
        }

        // Create the NeoMerx Encoder used to generate the JSON API string.
        // Please note that a third NeoMerx EncoderOptions argument `depth`
        // exists but has not been implemented in this plugin.
        $encoder = Encoder::instance(
            $schemas,
            new EncoderOptions(
                $this->_jsonOptions(),
                $this->viewVars['_urlPrefix']
            )
        );

        // Add optional top-level `version` node to the response
        if ($this->viewVars['_withJsonApiVersion']) {
            if (is_array($this->viewVars['_withJsonApiVersion'])) {
                $encoder->withJsonApiVersion($this->viewVars['_withJsonApiVersion']);
            } else {
                $encoder->withJsonApiVersion();
            }
        }

        // Add optional top-level `meta` node to the response
        if ($this->viewVars['_meta']) {
            if (empty($serialize)) {
                return $encoder->encodeMeta($this->viewVars['_meta']);
            } else {
                $encoder->withMeta($this->viewVars['_meta']);
            }
        }

        // Resources defined in `include` will appear in the included section,
        // resources defined in `fieldSets` are used to limit the fields shown
        // in the result.
        //
        // Please note that a configured `include` var will take precedence
        // over the `getIncludePaths()` method that MIGHT exist in a schema.
        $include = $this->viewVars['_include'];
        $fieldSets = $this->viewVars['_fieldSets'];

        $parameters = new EncodingParameters(
            $include,
            $fieldSets
        );

        // Let NeoMerx Encoder render the correct json string
        $json = $encoder->encodeData($serialize, $parameters);

        // Add top-level `query` node to the response only if user enabled the
        // setting AND we are in debug mode
        if (Configure::read('debug') && $this->viewVars['_debugQueryLog'] === true) {
            $json = json_decode($json, true);
            $json['query'] = $this->viewVars['_queryLogs'];
            $json = json_encode($json, $this->_jsonOptions());
        }

        // return JSON API json string
        return $json;
    }

    /**
     * Maps entity names to corresponding schema files if it exists, otherwise
     * uses the DynamicEntitySchema.
     *
     * @param array $entities List holding entity names that need to be mapped to a schema class
     * @throws \Crud\Error\Exception\CrudException
     * @return array A list with Entity class names as key holding NeoMerx Closure object
     */
    protected function _entitiesToNeoMerxSchema(array $entities)
    {
        $schemas = [];
        $entities = Hash::normalize($entities);
        foreach ($entities as $entityName => $options) {
            $entityClass = App::className($entityName, 'Model\Entity');

            if (!$entityClass) {
                throw new CrudException('JsonApiListener cannot not find Entity class ' . $entityName);
            }

            // If user created a schema file for the current Entity... use it
            $schemaClass = App::className($entityName, 'Schema\JsonApi', 'Schema');

            // Otherwise use the dynamic schema that comes with Crud
            if (!$schemaClass) {
                $schemaClass = App::className('Crud.DynamicEntity', 'Schema\JsonApi', 'Schema');
            }

            // Uses NeoMerx createSchemaFromClosure()` to generate Closure
            // object with schema information.
            $schema = function ($factory) use ($schemaClass, $entityName) {
                return new $schemaClass($factory, $this, $entityName);
            };

            // Add generated schema to the collection before processing next
            $schemas[$entityClass] = $schema;
        }

        return $schemas;
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
                array_flip($this->_specialVars)
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
