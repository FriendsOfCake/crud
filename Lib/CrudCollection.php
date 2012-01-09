<?php
App::uses('ObjectCollection', 'Utility');

class CrudCollection extends ObjectCollection {
    public function loadAll($items = array()) {
        $items = Set::normalize($items);
        foreach ($items as $component => $settings) {
            $settings = (array)$settings;
            $this->load($component, $settings);
        }
    }

    public function load($component, $settings = array()) {
        if (is_array($settings) && isset($settings['className'])) {
            $alias = $component;
            $component = $settings['className'];
        }

        list($plugin, $name) = pluginSplit($component, true);
        if (!isset($alias)) {
            $alias = $name;
        }

        if (isset($this->_loaded[$alias])) {
            return $this->_loaded[$alias];
        }

        $componentClass = $name . 'FormDecorator';
        App::uses($componentClass, $plugin . 'Form');
        if (!class_exists($componentClass)) {
            throw new CakeException(array(
                'file' => Inflector::underscore($componentClass) . '.php',
                'class' => $componentClass
            ));
        }

        $this->_loaded[$alias] = new $componentClass($this, $settings);
        $enable = isset($settings['enabled']) ? $settings['enabled'] : true;
        if ($enable === true) {
            $this->_enabled[] = $alias;
        }

        return $this->_loaded[$alias];
    }
}
