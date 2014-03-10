Crud.afterPaginate
^^^^^^^^^^^^^^^^^^

This event is triggered right after the call to ``Controller::paginate()``.

The ``items`` property of event object contains all the database record found in the pagination call.

Modify the result
-----------------

.. code-block:: phpinline

	public function index() {
		$this->Crud->on('afterPaginate', function(\Cake\Event\Event $event) {
			foreach ($event->subject->items as $item) {
				// $item is an entity
			}
		});

		return $this->Crud->execute();
	}
