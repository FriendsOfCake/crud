Installation
============

Installing composer is quick and simple

Requirements
============

* CakePHP 3.x
* PHP 5.4

Getting the source code
=======================

You can get the Crud source code using either composer or git

Using composer
--------------

The recommended installation method for this plugin is by using composer.

Using the inline require for composer

.. code-block:: sh

	composer require friendsofcake/crud:4.*

Or add this to your composer.json configuration:

.. code-block:: javascript

	{
		"require" : {
			"FriendsOfCake/crud": "4.*"
		}
	}


Using git submodule
-------------------

Or add it as a git module, this is recommended over `git clone` since it's easier to keep up to date with development that way

.. code-block:: sh

		git submodule add git://github.com/FriendsOfCake/crud.git Plugin/Crud

Loading the plugin
==================

Add the following to your /App/Config/bootstrap.php

.. code-block:: phpinline

	CakePlugin::load('Crud');

Configuring the controller
==========================

In your AppController add the following code

.. code-block:: php

	<?php
	namespace App\Controller;

	class AppController extends \Cake\Controller\Controller {

		# this line
		use Crud\Controller\ControllerTrait;

	}
