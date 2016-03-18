relatedModels
^^^^^^^^^^^^^

.. note::

    If you have the :doc:`RelatedModels listener </listeners/related-models>` configured, you can have Crud automatically load related data.

.. code-block:: phpinline

    <?php
    $this->Crud->listener('relatedModels')->relatedModels(true);

Find out more about the :doc:`RelatedModels listener </listeners/related-models>` in the :doc:`Listeners chapter </listeners>`.