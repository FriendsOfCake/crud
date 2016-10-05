Bulk
====

If you need to perform an action against a number of records, you can extend
the abstract ``Bulk\BaseAction`` class to create your own.

Three BulkAction classes exist in the core:

- :doc:`Delete</actions/bulk-delete>`: Deletes a set of entities
- :doc:`SetValue</actions/bulk-set-value>`: Sets a field to a value for a set of entities
- :doc:`Toggle</actions/bulk-toggle>`: Toggles the value of a boolean field for a set of entities

To create your own BulkAction, simply create a new action class with a ``_bulk``
method. This method takes a CakePHP ``Query`` object as it's first argument

.. code-block:: phpinline

  <?php
  namespace App\Crud\Action;

  use Cake\ORM\Query;
  use Crud\Action\Bulk\BaseAction;

  class ApproveAction extends BaseAction
  {
    /**
     * Set the value of the approved field to true
     * for a set of entities
     *
     * @param \Cake\ORM\Query $query The query to act upon
     * @return boolean
     */
    protected function _bulk(Query $query)
    {
      $query->update()->set(['approved' => true]);
      $statement = $query->execute();
      $statement->closeCursor();
      return $statement->rowCount();
    }
  }

Configuration
-------------

.. include:: /_partials/actions/configuration_intro.rst
.. include:: /_partials/actions/configuration/enabled.rst
.. include:: /_partials/actions/configuration/find_method.rst

Events
------

This is a list of events emitted from actions that extend ``Bulk\BaseAction``.

Please see the :doc:`events documentation</events>` for a full list of generic
properties and how to use the event system correctly.

.. include:: /_partials/events/startup.rst
.. include:: /_partials/events/before_filter.rst
.. include:: /_partials/events/before_bulk.rst
.. include:: /_partials/events/after_bulk.rst
.. include:: /_partials/events/set_flash.rst
.. include:: /_partials/events/before_redirect.rst
