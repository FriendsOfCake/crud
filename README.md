[![Build Status](https://travis-ci.org/FriendsOfCake/crud?branch=master)](https://travis-ci.org/FriendsOfCake/crud)
[![Coverage Status](https://coveralls.io/repos/FriendsOfCake/crud/badge.png?branch=master)](https://coveralls.io/r/FriendsOfCake/crud?branch=mater)
[![Total Downloads](https://poser.pugx.org/FriendsOfCake/crud/d/total.png)](https://packagist.org/packages/FriendsOfCake/crud)
[![Latest Stable Version](https://poser.pugx.org/FriendsOfCake/crud/v/stable.png)](https://packagist.org/packages/FriendsOfCake/crud)

# Introduction

Crud was built to be [scaffolding](http://book.cakephp.org/2.0/en/controllers/scaffolding.html) on steroids, and allow
developers to have enough flexibility to use it for both rapid prototyping and production applications, even on the same
code base - saving you even more time.

Crud is [very fast to install](http://cakephp.nu/cakephp-crud/docs/installation.html), 2 minutes top.

Crud is very flexible, and has tons of [configuration options](http://cakephp.nu/cakephp-crud/docs/configuration.html)

Crud aims to not get in your way, and if it happens to get in your way, you can change the behavior you don't like very
easily.

Crud relies heavily on Cake events and it's possible to override, extend or disable almost all of Crud's functionality,
either globally or for just one specific action.

Usually the basic code for controller CRUD actions is very simple, and always look the same - this plugin will add the
actions to your controller so you don't have to re-implement them over and over.

Crud does not have the same limitations as Cake's own scaffolding, which is 'their way or the highway'. Crud allows you
to hook into all stages of a request, only building the controller code needed specifically for your business logic,
outsourcing the all the heavy boilerplating to Crud.

Less boilerplate code means less code to maintain, and less code to spend time unit testing.

Crud allows you to both use your own views, from bake or hand-crafted, as well as only adding the code needed to fulfill
your application logic, using [events](http://cakephp.nu/cakephp-crud/docs/events.html). It is by default compatible with Cake's baked views.

Crud also provides built-in features for JSON and XML [API](http://cakephp.nu/cakephp-crud/docs/listeners/api.html) for any
action you have enabled through Crud - that means no more double work maintaining both a HTML frontend and a JSON and/or
XML interface for your applications - saving you tons of time and having a leaner code base.

# Documentation

There's [extensive documentation](http://cakephp.nu/cakephp-crud/docs/) and how-to guides available. If you're reading
this readme from a git checkout - you can read the docs offline by checking out the `gh-pages` branch.

# Bugs

If you happen to stumble upon a bug, please feel free to either create a pull request with a fix (optionally with a test),
a description of the bug and how it was resolved or just create an issue, with a description of the bug, and I'll see if
I can fix it asap.

# Features

If you have a good idea for a Crud feature, please chat me up on IRC, and let's discuss it - Pull Requests are always
more than welcome.

# Support / questions

You can always hit me up on IRC in the #cakephp channel - I lurk there as 'Jippi'
