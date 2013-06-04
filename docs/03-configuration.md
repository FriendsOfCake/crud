# Configuration

We only use routes without prefix in these examples, but the Crud component works with __any__ prefixes you may have. It just requires some additional configuration.

In the code example above, we pass in an actions array with all the controller actions we wish the Crud component to handle - you can easily omit some of the actions

```php
<?php
class AppController extends Controller {

	public $components = array(
		// Enable CRUD actions
		'Crud.Crud' => array(
			 // All actions but delete() will be implemented
			'actions' => array('index', 'add', 'edit', 'view')
		)
	);

}
?>
```

In the above example, if the `delete` action is called on __any__ controller, cake will raise it's normal missing action error as if nothing has happened

You can enable and disable Crud actions on the fly

```php
<?php
/**
 * Application wide controller
 *
 * @abstract
 * @package App.Controller
 */
abstract class AppController extends Controller {
/**
 * List of global controller components
 *
 * @var array
 */
	public $components = array(
		// Enable CRUD actions
		'Crud.Crud' => array(
			'actions' => array('index', 'add', 'edit', 'view', 'delete')
		)
	);

	public function beforeFilter() {
		// Will ignore delete action
		$this->Crud->disableAction('delete');

		// Will process delete action again
		$this->Crud->enableAction('delete');

		parent::beforeFilter();
	}
}
?>
```

You can also change the default view used for a Crud action

```php
<?php
class DemoController extends AppController {
	public function beforeFilter() {
		// Change the view for add crud action to be "public_add.ctp"
		$this->Crud->mapActionView('add',  'public_add');

		// Change the view for edit crud action to be "public_edit.ctp"
		$this->Crud->mapActionView('edit', 'public_edit');

		// Convenient shortcut to change both at once
		$this->Crud->mapActionView(array('add' => 'public_add', 'edit' => 'public_edit'));

		parent::beforeFilter();
	}
}
?>
```

# Examples

## Change the find() method

Changing the `find type` allows you to use [custom find methods](http://book.cakephp.org/2.0/en/models/retrieving-your-data.html#creating-custom-find-types) inside a `CrudAction`

By providing your own custom find method, you can easily inject more complex `find` logic and at the same time follow the [skinny controllers, fat models](http://www.mikebernat.com/images/cake/layercake.png) code guideline.

Adding custom data to your custom find is also possible, and very simple, have a look at the `events` documentation.

By default `index` uses `find('all')`

By default `view` uses `find('first')`

By default `add` uses `find('first')`

By default `edit` uses `find('first')`

By default `delete` uses `find('count')`

### Using beforeFilter

```php
public function beforeFilter() {
	// The index() function will call find('published') on your model
	$this->Crud->getAction('index')->findMethod('published');

	// Get the current configuration
	$config = $this->Crud->getAction('index')->findMethod();

	// The admin_index() function will call find('unpublished') on your model
	$this->Crud->getAction('admin_unpublished')->findMethod('unpublished');

	// Get the current configuration
	$config = $this->Crud->getAction('admin_index')->findMethod();
}
```

### In the controller action

```php
// You don't have to provide the action name in 'getAction'
// since the default is the current action
public function index() {
	$this->Crud->getAction()->findMethod('published');

	// Get the current configuration
	$config = $this->Crud->getAction()->findMethod();

	return $this->Crud->executeAction();
}

public function admin_index() {
	$this->Crud->getAction()->findMethod('unpublished');

	// Get the current configuration
	$config = $this->Crud->getAction()->findMethod();

	return $this->Crud->executeAction();
}
```

## Change the view to be rendered

By default Crud renders a view with the same name as the controller action, but there are cases where you want to change this.

If you action is `admin_index` the `admin_index.ctp` view will be rendered by default.

### Using beforeFilter

```php
public function beforeFilter() {
	$this->Crud->getAction('index')->view('my_index');

	// Get the current configuration
	$config = $this->Crud->getAction('index')->view();

	$this->Crud->getAction('admin_index')->view('my_admin_index');

	// Get the current configuration
	$config = $this->Crud->getAction('admin_index')->view();
}
```

### In the controller action

```php
// You don't have to provide the action name in 'getAction'
// since the default is the current action

public function index() {
	$this->Crud->getAction()->view('my_index');

	// Get the current configuration
	$config = $this->Crud->getAction()->view();

	return $this->Crud->executeAction();
}

public function admin_index() {
	$this->Crud->getAction()->view('my_admin_index');

	// Get the current configuration
	$config = $this->Crud->getAction()->view();

	return $this->Crud->executeAction();
}
```

## Enable a Crud action on the fly

This can only be done in `beforeFilter` (or earlier in the request) since it's the last method called
before the actual controller action is executed.

Enabling a `CrudAction` automatically injects it into the Controller as if it was defined in the `actions` array in the crud component configuration.

```php
// This can only be done in the beforeFilter

public function beforeFilter() {
	$this->Crud->getAction('delete')->enable();
	$this->Crud->getAction('admin_delete')->enable();
}
```

## Disable a Crud action on the fly

This can only be done in `beforeFilter` (or earlier in the request) since it's the last method called
before the actual controller action is executed.

Disabling a `CrudAction` automatically removes it from the Controller as if it was never defined in the `actions` array in the crud component configuration.

```php
// This can only be done in the beforeFilter

public function beforeFilter() {
	$this->Crud->getAction('delete')->disable();
	$this->Crud->getAction('admin_delete')->disable();
}
```

## Change CrudAction configuration settings on the fly

```php
// This can be done both in beforeFilter and the controller action
// All possible config keys can be found in the CrudAction classes (app/Plugin/Crud/Controller/Crud/Action)
public function beforeFilter() {
	$this->Crud->getAction('view')->config('validateId', 'uuid');

	// Get the current configuration
	$config = $this->Crud->getAction('view')->config('validateId');
}
```

## Disable secureDelete for delete() actions

`secureDelete` enforces that a `HTTP DELETE` request must be used for any `delete()` actions.

If set to `true` only `HTTP DELETE` is considered valid for `delete()` actions.

If set to `false` both `HTTP DELETE` and `HTTP POST` is considered valid for `delete()` actions.

The default setting is `true`

```php
// Disabling this feature allow HTTP POST to execute delete() actions
// This can be changed in both beforeFilter and the controller action
public function beforeFilter() {
	$this->Crud->getAction('delete')->config('secureDelete', false);

	// Get the current configuration
	$config = $this->Crud->getAction('delete')->config('secureDelete');
}
```

## Configure related model data for add / edit views

This feature automates the task of generating lists with related data
for your add / edit forms.
Each model relation you have will automatically be inflected to be FormHelper compatible, for example:

`YourModel hasAndBelongsToMany Tags` will be `$tags in the view

`YourModel belongsTo User` will be `$users` in the view

You can enable and disable which model relations you want to have automatically fetched very easily, as shown below.

If you set `relatedLists` to `true` all model relations will be fetched automatically.

If you set `relatedLists` to an `array`, only the related models in that array will be fetched automatically.

If you set `relatedLists` to `false` no model relations will be fetched automatically.

```php
// This can be changed in beforeFilter and the controller action
public function beforeFilter() {
	// Automatically executes find('list') on the User ($users) and Tag ($tags) models
	$this->Crud->getAction('add')->config('relatedLists', array('User', 'Tags'));

	// Automatically executes find('list') on the User ($users) model
	$this->Crud->getAction('add')->config('relatedLists', array('User'));

	// Fetch related data from all model relations (default)
	$this->Crud->getAction('add')->config('relatedLists', true);

	// Don't fetch any related data
	$this->Crud->getAction('add')->config('relatedLists', false);

	// Get the current configuration
	$config = $this->Crud->getAction('add')->config('relatedLists');
}
```

## Change save options

All options as described in [CakePHP documentation on saveAll()](http://book.cakephp.org/2.0/en/models/saving-your-data.html#model-saveall-array-data-null-array-options-array) is valid here

This configuration maps directly to the 2nd parameter of `saveAll()` called `$options`

The default for `add` and `edit` is `array('validate' => 'first', 'atomic' => true)`

```php
// This can be changed in beforeFilter and in a controller action
public function beforeFilter() {
	// saveOptions is the 2nd argument to saveAll()
	$this->Crud->getAction('add')->saveOptions(array('atomic' => false));
}
```
