<?php
namespace Crud\Error;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Error\Debugger;
use Crud\Listener\ApiQueryLogListener;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Exceptions\ErrorCollection;

/**
 * Exception renderer for the JsonApiListener
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiExceptionRenderer extends ExceptionRenderer
{

    /**
     * Method used for all non-validation errors.
     *
     * @param string $template Name of template to use (ignored for jsonapi)
     * @return \Cake\Network\Response
     */
    protected function _outputMessage($template)
    {
        if (!$this->controller->request->is('jsonapi')) {
            return parent::_outputMessage($template);
        }

        $viewVars = $this->controller->viewVars;

        $code = $viewVars['code']; // e.g. 404
        $title = $this->controller->response->httpCodes($code)[$code]; // e,g. Not Found

        // Only set JSON API `detail` field if `message` viewVar field is not
        // identical to the CakePHP HTTP Status Code description.
        $detail = null;
        if (!empty($viewVars['message']) && (strcasecmp($viewVars['message'], $title) !== 0)) {
            $detail = $viewVars['message'];
        }

        $errorCollection = new ErrorCollection();
        $errorCollection->add(new Error(
            $idx = null,
            $aboutLink = null,
            $status = null,
            $code,
            $title,
            $detail,
            $source = null,
            $meta = null
        ));

        $encoder = Encoder::instance();
        $json = $encoder->encodeErrors($errorCollection);

        if (Configure::read('debug')) {
            $json = $this->_addDebugNode($json);
            $json = $this->_addQueryLogsNode($json);
        }

        // send response
        $this->controller->response->type('jsonapi');
        $this->controller->response->body($json);

        return $this->controller->response;
    }

    /**
     * Method used for rendering 422 validation used for both CakePHP entity
     * validation errors and JSON API (request data) documents.
     *
     * @param \Crud\Error\Exception\ValidationException $exception Exception
     * @return \Cake\Network\Response
     */
    public function validation($exception)
    {
        if (!$this->controller->request->is('jsonapi')) {
            return parent::validation($exception);
        }

        $status = $exception->getCode();

        try {
            $this->controller->response->statusCode($status);
        } catch (Exception $e) {
            $status = 422;
            $this->controller->response->statusCode($status);
        }

        $errorCollection = $this->_getNeoMerxErrorCollection($exception->getValidationErrors());

        $encoder = Encoder::instance();
        $json = $encoder->encodeErrors($errorCollection);

        if (Configure::read('debug')) {
            $json = $this->_addDebugNode($json);
            $json = $this->_addQueryLogsNode($json);
        }

        // set data and send response
        $this->controller->response->type('jsonapi');
        $this->controller->response->body($json);

        return $this->controller->response;
    }

    /**
     * Returns a NeoMerx ErrorCollection with validation errors by either:
     *
     * - returning cloaked collection as passed down from the Listener
     * - creating a new collection from CakePHP validation errors
     *
     * @param array $validationErrors CakePHP validation errors
     * @return \Neomerx\JsonApi\Exceptions\ErrorCollection
     */
    protected function _getNeoMerxErrorCollection($validationErrors)
    {
        if (isset($validationErrors['CrudJsonApiListener']['NeoMerxErrorCollection'])) {
            if (is_a($validationErrors['CrudJsonApiListener']['NeoMerxErrorCollection'], '\Neomerx\JsonApi\Exceptions\ErrorCollection')) {
                return $validationErrors['CrudJsonApiListener']['NeoMerxErrorCollection'];
            }
        }

        // Create new NeoMerx ErrorCollection from CakePHP validation errors
        $errorCollection = new ErrorCollection();

        $validationErrors = $this->_standardizeValidationErrors($validationErrors);

        foreach ($validationErrors as $validationError) {
            $errorCollection->addDataAttributeError(
                $name = $validationError['fields'][0],
                $title = $validationError['name'],
                $detail = $validationError['message'],
                $status = null,
                $idx = null,
                $aboutLink = null,
                $code = null,
                $meta = null
            );
        }

        return $errorCollection;
    }

    /**
     * Adds top-level `debug` node to a json encoded string
     *
     * @param string $json Json encoded string
     * @return string Json encoded string with added debug node
     */
    protected function _addDebugNode($json)
    {
        $viewVars = $this->controller->viewVars;

        if (empty($viewVars['error'])) {
            return $json;
        }

        $debug = [];
        $debug['class'] = get_class($viewVars['error']);

        if (!isset($debug['trace'])) {
            $debug['trace'] = Debugger::formatTrace($viewVars['error']->getTrace(), [
                'format' => 'array',
                'args' => false
            ]);
        }

        $result = json_decode($json, true);
        $result['debug'] = $debug;

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    /**
     * Add top-level `query` node if ApiQueryLogListener is loaded.
     *
     * @param string $json Json encoded string
     * @return string Json encoded string
     */
    protected function _addQueryLogsNode($json)
    {
        $listener = $this->_getApiQueryLogListenerObject();
        $logs = $listener->getQueryLogs();

        if (empty($logs)) {
            return $json;
        }

        $result = json_decode($json, true);
        $result['query'] = $logs;

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    /**
     * Returns a plain ApiQueryLogListener instance for e.g. unit testing purposes.
     *
     * @return \Crud\Listener\ApiQueryLogListener
     */
    protected function _getApiQueryLogListenerObject()
    {
        return new ApiQueryLogListener(new Controller());
    }

    /**
     * Creates a uniform array with all information required to generate
     * NeoMerx DataAttributeErrors by parsing (differently structured) built-in
     * and user-defined CakePHP rules feedback.
     *
     * Note: we need this function because Cake's built-in rules don't pass
     * through `_processRules()` function in the Validator.
     *
     * @param array $errors CakePHP validation errors
     * @return array Standardized array
     */
    protected function _standardizeValidationErrors($errors = [])
    {
        $result = [];

        foreach ($errors as $field => $validationFeedback) {
            // must be a user defined rule
            if (is_int(key($validationFeedback))) {
                $result[] = $validationFeedback[0];
                continue;
            }

            // stil here so array key must be a string (and thus a built-in rule)
            $rule = key($validationFeedback);
            $message = $validationFeedback[$rule];

            $result[] = [
                'fields' => [$field],
                'name' => $rule,
                'message' => $message
            ];
        }

        return $result;
    }
}
