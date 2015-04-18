Introduction
============

CRUD was built to be `scaffolding <http://book.cakephp.org/2.0/en/controllers/scaffolding.html>`_
on steroids, and allow developers to have enough flexibility to use it for both
rapid prototyping and production applications, even on the same code base --
saving you time.

Why Use Crud
------------

* CRUD is :doc:`very fast to install</installation>`, a few minutes tops.

* CRUD is very flexible and has tons of :doc:`configuration options</configuration>` (but very sane defaults, just like CakePHP).

* CRUD aims to stay out of your way, and if it happens to get in your way, you can change the undesired behavior very easily.

* CRUD relies heavily on CakePHP events making it possible to override, extend, or disable almost all of CRUD's functionality either globally or for one specific action.

* CRUD removes the boilerplate code from your controllers, which mean less code to maintain, and less code to spend time unit testing.

* CRUD will dynamically add the actions to your controller so you don't have to re-implement them over and over again.

* CRUD does not have the same limitations as CakePHP's own scaffolding, which is "my way or the highway." CRUD allows you to hook into all stages of a request, only building the controller code needed specifically for your business logic, outsourcing all the heavy boiler-plating to CRUD.

* CRUD allows you to use your own views, baked or hand-crafted, in addition to adding the code needed to fulfill your application logic, using :doc:`events<events>`. It is by default compatible with CakePHP's baked views.

* CRUD also provides built in features for JSON :doc:`API<listener/api>` for any action you have enabled through CRUD, which eliminates maintaining both a HTML frontend and a JSON and/or XML interface for your applications -- saving you tons of time and having a leaner code base.

* CRUD uses the MIT license, just like CakePHP.

Bugs
----

If you happen to stumble upon a bug, please feel free to create a pull request with a fix
(optionally with a test), and a description of the bug and how it was resolved.

You can also create an issue with a description to raise awareness of the bug.

Features
--------

If you have a good idea for a Crud feature, please join us on IRC and let's discuss it. Pull
requests are always more than welcome.

Support / Questions
-------------------

You can join us on IRC in the #FriendsOfCake channel on irc.freenode.net for any support or questions.
