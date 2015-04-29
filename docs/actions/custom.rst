Custom
======

If you are not satisfied with the :doc:`Actions</actions>` bundled with CRUD -
you can easily add your own.

A Crud Action can respond to any ``HTTP`` verb (``GET``, ``POST``, ``PUT``, ``DELETE``).
Each HTTP verb can be implemented as method, e.g. _get() for HTTP ``GET``,
_post() for HTTP ``POST`` and _put() for HTTP ``PUT``.

If no HTTP verb specific method is found in the class, ``_handle()`` will be executed.

.. code:: php

	<?php
	namespace App\Crud\Action;

	class Index extends \Crud\Action\BaseAction {

		/**
		* Generic handler for all HTTP verbs
		*
		* @return void
		*/
		protected function _handle() {

		}

	}
