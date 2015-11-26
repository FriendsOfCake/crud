Installation
============

Installing composer is quick and simple.

Requirements
------------

* CakePHP 3.x
* PHP 5.4

Getting the source code
-----------------------

You can get the Crud source code using either composer or git.

Using composer
--------------

The recommended installation method for this plugin is by using composer.

Using the inline require for composer:

.. code-block:: sh

	composer require friendsofcake/crud:~4.2

Or add this to your composer.json configuration:

.. code-block:: javascript

	{
		"require" : {
			"FriendsOfCake/crud": "~4.2"
		}
	}


Using git submodule
-------------------

Or add it as a git module, this is recommended over ``git clone`` since it's
easier to keep up to date with development that way:

.. code-block:: sh

		git submodule add git://github.com/FriendsOfCake/crud.git Plugin/Crud
		cd Plugin/Crud


Loading the plugin
------------------

Add the following to your /App/Config/bootstrap.php

.. code-block:: phpinline

	Plugin::load('Crud');


Configuring the controller
--------------------------

In your AppController add the following code:

.. code-block:: php

	<?php
	namespace App\Controller;

	class AppController extends \Cake\Controller\Controller {

		use \Crud\Controller\ControllerTrait;

	}

.. note::

	It's not required to add the ``ControllerTrait`` to ``AppController`` - you can add it to any specific controller
	as well if you don't want Crud installed application wide

Adding the ``ControllerTrait`` itself do not enable anything CRUD, but simply installs the code to handle
the ``\Cake\Error\MissingActionException`` exception so you don't have to implement an action in your controller
for Crud to work. This will make a lot of sense later.

The :doc:`Configuration page</configuration>` explains how to setup and configure the Crud component.
