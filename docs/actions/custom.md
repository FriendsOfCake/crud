---
title: Actions - Custom
layout: default
---

# Crud Actions

A `CrudAction` is a class that handles a specific kind of crud action type (index, add, edit, view,
delete) in isolation.

Each `CrudAction` have it's own unique configuration and events it uses.

If you don't like how a specific `CrudAction` behaves, you can very easily replace it with your own.

# Custom Crud Actions

## Caveat

The class names `Index`, `View`, `Add`, `Edit` and `Delete` is reserved for CrudComponent

You can't create your own versions of these CrudActions with the same name.

If you want to change the behavior of `Index` simply rename it to `MyIndex` and remap your
controller action to the correct CrudAction object using `mapAction()` in CrudComponent.
