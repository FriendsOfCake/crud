<?php
declare(strict_types=1);

namespace Crud;

use Cake\Core\BasePlugin;

class CrudPlugin extends BasePlugin
{
    /**
     * Do bootstrapping or not
     *
     * @var bool
     */
    protected bool $bootstrapEnabled = false;

    /**
     * Load routes or not
     *
     * @var bool
     */
    protected bool $routesEnabled = false;
}
