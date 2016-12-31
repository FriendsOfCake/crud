<?php
namespace Crud\Traits;

use Cake\ORM\Entity;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

trait JsonApiTrait
{

    /**
     * Use Cake's router to generate all (prefix) URL parts used in the
     * NeoMerx Link objects to ensure proper handling of prefixes, base, etc.
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @param bool $absolute True for absolute links, false for relative links
     * @return string
     */
    protected function _getCakeSubUrl(Entity $entity, $absolute = true)
    {
        $controller = $this->_getControllerNameFromEntity($entity);

        if ($absolute === true) {
            return Router::url([
                'controller' => $controller,
                '_method' => 'GET',
            ], true);
        }

        return Router::normalize([
            'controller' => $controller,
            '_method' => 'GET',
        ], true);
    }

    /**
     * Parses the name of an Entity class to build a lowercase plural
     * controller name to be used in links.
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @return string Lowercase controller name
     */
    protected function _getControllerNameFromEntity($entity)
    {
        $className = $this->_getClassName($entity);
        $className = Inflector::pluralize($className);

        return Inflector::tableize($className);
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
