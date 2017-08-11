<?php
namespace Crud\Event;

/**
 * Crud subject
 *
 * All Crud.* events passes this object as subject
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class Subject
{

    /**
     * List of events this subject has passed through
     *
     * @var array
     */
    protected $_events = [];

    /**
     * Constructor
     *
     * @param array $fields Fields
     */
    public function __construct($fields = [])
    {
        $this->set($fields);
    }

    /**
     * Add an event name to the list of events this subject has passed through
     *
     * @param string $name name of event
     * @return void
     */
    public function addEvent($name)
    {
        $this->_events[] = $name;
    }

    /**
     * Returns the list of events this subject has passed through
     *
     * @return array
     */
    public function getEvents()
    {
        return $this->_events;
    }

    /**
     * Returns whether the specified event is in the list of events
     * this subject has passed through
     *
     * @param string $name name of event
     * @return array
     */
    public function hasEvent($name)
    {
        return in_array($name, $this->_events);
    }

    /**
     * Set a list of key / values for this object
     *
     * @param array $fields Fields
     * @return \Crud\Event\Subject
     */
    public function set($fields)
    {
        foreach ($fields as $k => $v) {
            $this->{$k} = $v;
        }

        return $this;
    }

    /**
     * Check if the called action is white listed or blacklisted
     * depending on the mode
     *
     * Modes:
     * only => only if in array (white list)
     * not  => only if NOT in array (blacklist)
     *
     * @param string $mode Mode
     * @param mixed $actions Actions list
     * @return bool
     * @throws \Exception In case of invalid mode
     */
    public function shouldProcess($mode, $actions = [])
    {
        if (is_string($actions)) {
            $actions = [$actions];
        }

        switch ($mode) {
            case 'only':
                return in_array($this->action, $actions);

            case 'not':
                return !in_array($this->action, $actions);

            default:
                throw new \Exception('Invalid mode');
        }
    }
}
