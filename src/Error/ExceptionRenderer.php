<?php
declare(strict_types=1);

namespace Crud\Error;

use Cake\Core\Configure;
use Cake\Error\Debugger;
use Cake\Error\Renderer\WebExceptionRenderer;
use Cake\Http\Response;
use Crud\Error\Exception\ValidationException;
use Crud\Traits\QueryLogTrait;
use Exception;
use function Cake\Core\h;

/**
 * Exception renderer for ApiListener
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class ExceptionRenderer extends WebExceptionRenderer
{
    use QueryLogTrait;

    /**
     * Renders validation errors and sends a 422 error code
     *
     * @param \Crud\Error\Exception\ValidationException $error Exception instance
     * @return \Cake\Http\Response
     */
    public function validation(ValidationException $error): Response
    {
        $url = $this->controller->getRequest()->getRequestTarget();
        /** @var int $status */
        $status = $code = $error->getCode();
        try {
            $this->controller->setResponse($this->controller->getResponse()->withStatus($status));
        } catch (Exception $e) {
            $status = 422;
            $this->controller->setResponse($this->controller->getResponse()->withStatus($status));
        }

        $sets = [
            'code' => $code,
            'url' => h($url),
            'message' => $error->getMessage(),
            'error' => $error,
            'errorCount' => $error->getValidationErrorCount(),
            'errors' => $error->getValidationErrors(),
        ];
        $this->controller->set($sets);
        $this->controller->viewBuilder()->setOption(
            'serialize',
            ['code', 'url', 'message', 'errorCount', 'errors'],
        );

        return $this->_outputMessage('error400');
    }

    /**
     * Generate the response using the controller object.
     *
     * If there is no specific template for the raised error (normally there won't be one)
     * swallow the missing view exception and just use the standard
     * error format. This prevents throwing an unknown Exception and seeing instead
     * a MissingView exception
     *
     * @param string $template The template to render.
     * @param bool $skipControllerCheck Skip checking controller for existence of
     *   method matching the exception name.
     * @return \Cake\Http\Response A response object that can be sent.
     */
    protected function _outputMessage(string $template, bool $skipControllerCheck = false): Response
    {
        $viewVars = ['success', 'data'];
        $this->controller->set('success', false);
        $this->controller->set('data', $this->_getErrorData());
        if (Configure::read('debug')) {
            $queryLog = $this->_getQueryLog();
            if ($queryLog) {
                $this->controller->set(compact('queryLog'));
                $viewVars[] = 'queryLog';
            }
        }
        $this->controller->viewBuilder()->setOption('serialize', $viewVars);

        return parent::_outputMessage($template, $skipControllerCheck);
    }

    /**
     * Helper method used to generate extra debugging data into the error template
     *
     * @return array debugging data
     */
    protected function _getErrorData(): array
    {
        $data = [];

        $viewVars = $this->controller->viewBuilder()->getVars();
        $serialize = $this->controller->viewBuilder()->getOption('serialize');
        if (!empty($serialize)) {
            foreach ($serialize as $v) {
                $data[$v] = $viewVars[$v];
            }
        }

        if (!empty($viewVars['error']) && Configure::read('debug')) {
            $data['exception'] = [
                'class' => get_class($viewVars['error']),
                'code' => $viewVars['error']->getCode(),
                'message' => $viewVars['error']->getMessage(),
            ];

            if (!isset($data['trace'])) {
                $data['trace'] = Debugger::formatTrace($viewVars['error']->getTrace(), [
                    'format' => 'array',
                    'args' => false,
                ]);
            }
        }

        return $data;
    }
}
