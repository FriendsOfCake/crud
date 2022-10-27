<?php
declare(strict_types=1);

namespace Crud\Error\Exception;

use Cake\Core\Exception\CakeException;
use Psr\Http\Message\ResponseInterface;

class CrudException extends CakeException
{
    /**
     * @var \Psr\Http\Message\ResponseInterface|null
     */
    protected $response;

    /**
     * Set response instance.
     *
     * @param \Psr\Http\Message\ResponseInterface $response Response instance.
     * @return void
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * Get response instance.
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
