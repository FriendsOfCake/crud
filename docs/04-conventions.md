# Convention

The Crud component always operates on the `$modelClass` of your controller, that's the first model in your `$uses` array

# View variable names

## Index

The result of `paginate()` in the `Index` Crud Action is by default called `$items`

You have two ways of making this compatible with baked views.

### Change the viewVar name in the controller

This method has the advantage of persisting if you later decide to re-bake your views

```php
<?php
public function beforeFilter() {
	parent::beforeFilter();
	$this->Crud->viewVar('index', 'users');
}
?>
```

### Rename the variable in the view

```php
<?php
// Users/index.ctp
$users = $items;
?>
```

## View

The result of `find()` in the `View` Crud Action is by default called `$item`

You have two ways of making this compatible with baked views.

## Change the viewVar name in the controller

This method has the advantage of persisting if you later decide to re-bake your views

```php
<?php
public function beforeFilter() {
	parent::beforeFilter();
	$this->Crud->viewVar('view', 'user');
}
?>
```

### Rename the variable in the view

```php
<?php
// Users/view.ctp
$user = $items;
?>
```

## Error and Success elements

This plugin uses `error.ctp` and `success.ctp` to display Flash messages.

So create the following:
* `Views/Elements/error.ctp`
* `Views/Elements/success.ctp`

In each, the message passed is in the variable `$message` as usual.
