## Convention

The Crud component always operates on the __$modelClass__ of your controller, that's the first model in your $uses array

There is no view for delete action, it will always redirect

Default `$components` variable according to the documentation is like this:

```php
public $components = array(
    // Enable CRUD actions
    'Crud.Crud' => array(
        'actions' => array('index', 'add', 'edit', 'view', 'delete')
    )
);
```

In this case, it will assume that all the IDs of your models are made with integers.

If they are `UUID indexed`, please add the following setting:

```php
public $components = array(
    // Enable CRUD actions
    'Crud.Crud' => array(
        'actions' => array('index', 'add', 'edit', 'view', 'delete'),
        'validateId' => 'uuid'
    )
);
```

In the `Index` views:

The paginated array is in $items. So if you have Baked an Index view, set in the beginning something like this:

```php
$users = $items;
```

The rest of the view will work. Otherwise you will get an error message about undefined variable $users.

In the `View` views:

Add the following at the beginning of the page.

```php
$user = $item;
```

## Error and Success elements

This plugin uses `error.ctp` and `success.ctp` to display Flash messages.

So create the following:
* `Views/Elements/error.ctp`
* `Views/Elements/success.ctp`

In each, the message passed is in the variable `$message` as usual.

# Event system

The CRUD plugin uses the new event system introduced in Cake 2.1
