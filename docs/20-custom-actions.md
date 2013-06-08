# Custom Crud Actions

## Caveat

The class names `Index`, `View`, `Add`, `Edit` and `Delete` is reserved for CrudComponent

You can't create your own versions of these CrudActions with the same name.

If you want to change the behavior of `Index` simply rename it to `MyIndex` and remap your controller action to the correct CrudAction object using `mapAction()` in CrudComponent.
