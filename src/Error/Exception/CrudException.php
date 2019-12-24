<?php
declare(strict_types=1);

namespace Crud\Error\Exception;

use Psr\Http\Message\ResponseInterface;

class CrudException extends \Cake\Core\Exception\Exception
{
    /**
     * @var \Psr\Http\Message\ResponseInterface|null
     */
    protected $response;

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
