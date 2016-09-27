<?php
namespace Crud\View;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Exception\MissingEntityException;
use Cake\Utility\Hash;
use Cake\View\View;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;

class JsonApiView extends View
{
    /**
     * List of special view vars. See Crud JsonApiListener's `$_defaultConfig`
     * for a full list and explanation of all configuration options.
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
        '_jsonp',
    ];

    /**
     * Constructor
     *
     * @param \Cake\Network\Request $request Request instance.
     * @param \Cake\Network\Response $response Response instance.
     * @param \Cake\Event\EventManager $eventManager EventManager instance.
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
     * @return string The serialized data
     * @throws MissingViewVarException when required view variable was not set
     */
    public function render($view = null, $layout = null)
    {
        $schemas = $this->_entitiesToNeoMerxSchema($this->viewVars['_entities']);

        $links = [];
        if (isset($this->viewVars['_links'])) {
            $links = $this->viewVars['_links'];
        }

        $serialize = null;
        if (isset($this->viewVars['_serialize'])) {
            $serialize = $this->viewVars['_serialize'];
        }

        if (isset($this->viewVars['_serialize']) && $this->viewVars['_serialize'] !== false) {
            $serialize = $this->_dataToSerialize($this->viewVars['_serialize']);
        }

        $jsonOptions = $this->_jsonOptions();
        $encoderOptions = new EncoderOptions($jsonOptions, $this->viewVars['_urlPrefix']);
        $encoder = Encoder::instance($schemas, $encoderOptions);

        // Add top-level node `version` to the response if configured
        if (isset($this->viewVars['_withJsonApiVersion'])) {
            if (is_array($this->viewVars['_withJsonApiVersion'])) {
                $encoder->withJsonApiVersion($this->viewVars['_withJsonApiVersion']);
            } else {
                $encoder->withJsonApiVersion();
            }
        }

        // Add top-level node `meta` to the response if configured.
        if (isset($this->viewVars['_meta'])) {
            if (empty($serialize)) {
                return $encoder->encodeMeta($this->viewVars['_meta']);
            } else {
                $encoder->withMeta($this->viewVars['_meta']);
            }
        }

        // Resources defined in `included` will appear in the included section,
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

        $json = $encoder->encodeData($serialize, $parameters);

        if (Configure::read('debug')) {
            $json = json_decode($json, true);
            $json['query'] = $this->viewVars['_queryLog'];
            $json = json_encode($json);
        }

        return $json; //$encoder->encodeData($serialize, $parameters);
    }

    /**
     * Maps entity names to corresponding schema files if it exists, otherwise
     * uses the DynamicEntitySchema.
     *
     * @param array $entities Flat array holding entity names that need to be mapped
     *  to a schema class
     * If the schema class does not exist, the default DynamicEntitySchema will be used.
     *
     * @return array A list of Entity class names as its key and a closure returning the schema class
     * @throws MissingViewVarException when the _entities view variable is empty
     * @throws MissingEntityException when defined entity class was not found in entities array
     */
    protected function _entitiesToNeoMerxSchema(array $entities)
    {
        if (empty($entities)) {
            throw new MissingViewVarException(['_entities']);
        }

        $schemas = [];
        $entities = Hash::normalize($entities);
        foreach ($entities as $entityName => $options) {
            $entityClass = App::className($entityName, 'Model\Entity');

            if (!$entityClass) {
                throw new MissingEntityException([$entityName]);
            }

            // If user created a schema file use it
            $schemaClass = App::className($entityName, 'Schema\JsonApi', 'Schema');

            // otherwise use the dynamic schema that comes with Crud
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
    protected function _dataToSerialize($serialize = true)
    {
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

        if (is_object($serialize)) {
            trigger_error('Assigning and object to "_serialize" is deprecated, assign the object to its own variable and assign "_serialize" = true instead.', E_USER_DEPRECATED);

            return $serialize;
        }

        if (is_array($serialize)) {
            $serialize = current($serialize);
        }

        return isset($this->viewVars[$serialize]) ? $this->viewVars[$serialize] : null;
    }

    /**
     * Return json options
     *
     * ### Special parameters
     * `_jsonOptions` You can set custom options for json_encode() this way,
     *   e.g. `JSON_HEX_TAG | JSON_HEX_APOS`.
     *
     * @return int json option constant
     */
    protected function _jsonOptions()
    {
        $jsonOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        if (isset($this->viewVars['_jsonOptions'])) {
            if ($this->viewVars['_jsonOptions'] === false) {
                $jsonOptions = 0;
            } else {
                $jsonOptions = $this->viewVars['_jsonOptions'];
            }
        }

        if (Configure::read('debug')) {
            $jsonOptions = $jsonOptions | JSON_PRETTY_PRINT;
        }

        return $jsonOptions;
    }
}
