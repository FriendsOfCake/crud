<?php
namespace Crud\Error;

use Cake\Core\Configure;
use Cake\Error\Debugger;
use Crud\Traits\QueryLogTrait;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Exceptions\ErrorCollection;

/**
 * Exception renderer for JsonApiListener
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class JsonApiExceptionRenderer extends \Cake\Error\ExceptionRenderer
{
    use QueryLogTrait;

    /**
     * Render JSON API error responses for all non-validation errors and send
     * corresponding error code.
     *
     * @param string $template Name of template to use (ignored for jsonapi)
     * @return \Cake\Network\Response
     */
    protected function _outputMessage($template)
    {
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
            null, // idx
            null, // LinkInterface
            null, // status
            $code,
            $title,
            $detail,
            null, // source (array)
            null // meta (array)
        ));

        $encoder = Encoder::instance();
        $json = $encoder->encodeErrors($errorCollection);

        if (Configure::read('debug')) {
            $json = $this->_addDebugNode($json);
            $json = $this->_addQueryNode($json);
        }

        // send response
        $this->controller->response->type('jsonapi');
        $this->controller->response->body($json);

        return $this->controller->response;
    }

    /**
     * Render JSON API error responses for validation errors with 422 error code.
     *
     * @param \Crud\Error\Exception\ValidationException $exception Exception instance
     * @return \Cake\Network\Response
     */
    public function validation($exception)
    {
        $status = $exception->getCode();

        try {
            $this->controller->response->statusCode($status);
        } catch (Exception $e) {
            $status = 422;
            $this->controller->response->statusCode($status);
        }

        $errorCollection = new ErrorCollection();

        $validationErrors = $this->_standardizeValidationErrors($exception->getValidationErrors());

        foreach ($validationErrors as $validationError) {
            $errorCollection->addDataAttributeError(
                $validationError['fields'][0], // name of invalidated field
                $validationError['name'], // title, validation rule
                $validationError['message'], // $detail, validation message
                null, // status
                null, // idx
                null, // LinkInterface $aboutLink
                null, // code
                null // meta
            );
        }

        $encoder = Encoder::instance();
        $json = $encoder->encodeErrors($errorCollection);

        if (Configure::read('debug')) {
            $json = $this->_addDebugNode($json);
            $json = $this->_addQueryNode($json);
        }

        // set data and send response
        $this->controller->response->type('jsonapi');
        $this->controller->response->body($json);

        return $this->controller->response;
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

        return json_encode($result);
    }

    /**
     * Adds top-level `query` node to a json encoded string
     *
     * @param string $json Json encoded string
     * @return string Json encoded string with added query node
     */
    protected function _addQueryNode($json)
    {
        $result = json_decode($json, true);
        $result['query'] = $this->_getQueryLog();

        return json_encode($result);
    }

    /**
     * Creates a uniform array with all information required to generate
     * NeoMerx AttributeErrors by parsing (differently structured) built-in
     * and user-defined failing rules feedback.
     *
     * Note: we need this function because Cake's built-in rules don't pass
     * through `_processRules()` function in the Validator.
     *
     * @param array $errors Array validation errors
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
