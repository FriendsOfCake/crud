<?php
declare(strict_types=1);

namespace Crud\Traits;

trait SerializeTrait
{
    /**
     * Change the serialize keys
     *
     * If `$keys` is NULL the current configuration is returned
     * else the `$serialize` configuration is changed.
     *
     * @param array|null $keys Keys to serialize
     * @return mixed
     */
    public function serialize(?array $keys = null): mixed
    {
        if ($keys === null) {
            return (array)$this->getConfig('serialize');
        }

        return $this->setConfig('serialize', $keys);
    }
}
