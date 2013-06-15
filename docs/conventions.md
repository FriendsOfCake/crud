---
title: Conventions
layout: default
---

# Conventions

The Crud component always operates on the `$modelClass` of your controller, that's the first model in your `$uses` array

Crud follows the CakePHP bake / scaffold conventions by default.

`UsersController::index()` will have a `$users` property with the paginated list of users

`UsersController::view()` will have a `$user` property with the single user
