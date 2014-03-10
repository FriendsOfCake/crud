Crud.beforePaginate
^^^^^^^^^^^^^^^^^^^

Triggered before ``Controller::paginate()`` is called.

The ``paginator`` property is a reference to the ``PaginatorComponent``.

If you wish to modify the pagination settings, you should **only** modify ``$event->subject->paginator->settings``.

Modifying ``Controller::$paginate`` will not have any effect during this callback.

Add conditions
--------------

.. code-block:: phpinline

	public function index() {
		$this->Crud->on('beforePaginate', function(\Cake\Event\Event $event) {
			$this->Paginate->settings['conditions']['is_active'] = true;
		});

		return $this->Crud->execute();
	}
