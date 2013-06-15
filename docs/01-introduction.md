# Introduction

Crud was build to be [scaffolding](http://book.cakephp.org/2.0/en/controllers/scaffolding.html) on steroids, and allow developers to have enough flexibility to use it for both rapid prototyping and production applications, even on the same code base - saving you even more time.

Usually the basic code for controller CRUD actions is very simple, and always look the same - this plugin will add the actions to your controller so you don't have to re-implement them over and over

Crud does not have the same limitations as Cake's own scaffolding, which is 'their way or the highway'. Crud allows you to hook into all stages of a request, only building the controller code needed specifically for your business logic, outsourcing the all the heavy boilerplating to Crud.

Less boilerplate code means less code to maintain, and less code to spend time unit testing.

Crud allows you to both use your own views, from bake or hand-crafted, as well as only adding the code needed to fulfill your application logic, using [events](05-events.md). It is by default compatible with Cake's baked views.

Crud is relying heavily on Cake events, and it's possible to override, extend or disable almost all of Cruds functionality, either globally or for just one specific action.

Crud also provides build in features for JSON and XML [API](08-api.md) for any action you have enabled through Crud - that means no more double work maintaining both a HTML frontend and a JSON and/or XML interface for your applications - saving you tons of time and having a leaner code base.

Crud aims to not get in your way, and if it happens to get in your way, you can change the behavior you don't like very easily.

Crud is [very fast to install](02-installation.md), 2 minutes top.

Crud is very flexible, and have tons of [configuration options](03-configuration.md)
