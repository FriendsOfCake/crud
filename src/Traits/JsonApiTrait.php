<?php
namespace Crud\Traits;

use Cake\Utility\Inflector;

trait JsonApiTrait
{

    /**
     * Parses the name of an Entity class to build a lowercase plural
     * controller name to be used in links.
     *
     * @param \Cake\Datasource\RepositoryInterface $repository Repository
     * @return string Lowercase controller name
     */
    protected function _getRepositoryRoutingParameters($repository)
    {
        list(, $controllerName) = pluginSplit($repository->registryAlias());

        return [
            'controller' => $controllerName,
        ];
    }

    /**
     * Helper function to return the class name of an object without namespace.
     *
     * @param mixed $class Any php class object
     * @return bool|string False if the classname could not be derived
     */
    protected function _getClassName($class)
    {
        if (!is_object($class)) {
            return false;
        }

        $className = get_class($class);

        if ($pos = strrpos($className, '\\')) {
            return substr($className, $pos + 1);
        }

        return $className;
    }

    /**
     * Helper function to determine if string is singular or plural.
     *
     * @param string $string Preferably a CakePHP generated name.
     * @return bool
     */
    protected function _stringIsSingular($string)
    {
        if (Inflector::singularize($string) === $string) {
            return true;
        }

        return false;
    }
}
