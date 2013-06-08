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
			'actions' => array(
				// The controller action 'index' will map to the IndexCrudAction
				'index' => 'Crud.Index',
				// The controller action 'add' will map to the AddCrudAction
				'add' 	=> 'Crud.Add',
				// The controller action 'edit' will map to the EditCrudAction
				'edit' 	=> 'Crud.edit',
				// The controller action 'view' will map to the ViewCrudAction
				'view' 	=> 'Crud.View'
			)
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

# Crud Actions

A `CrudAction` is a class that handles a specific kind of crud action type (index, add, edit, view, delete) in isolation.

Each CrudAction have it's own unique configuration and events it uses.

If you don't like how a specific CurdAction behaves, you can very easily replace it with your own

## Index CrudAction

The `index` CrudAction paginates over the primary model in the controller.

The source code can be found here: `Controller/Crud/Action/IndexCrudAction.php`

### Events

<table>
<thead>
	<tr>
		<th>Event</th>
		<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforePaginate</td>
		<td>$subject->controller->paginate</td>
		<td>Executed before the pagination call is made. You can modify the pagination array like you normally would in your model inside this event</td>
	</tr>
	<tr>
		<td>Crud.afterPaginate</td>
		<td>$subject->items</td>
		<td>Executed after the pagination call is made. You can modify the items from the database here.</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered. This is also before the controllers own beforeRender callback</td>
	</tr>
</tbody>
</table>

## Add CrudAction

The `add` CrudAction will create a record if the request is `HTTP POST` and the data is valid.

The source code can be found here: `Controller/Crud/Action/AddCrudAction.php`

### Events

<table>
<thead>
	<tr>
		<th>Event</th>
		<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforeSave</td>
		<td>N/A</td>
		<td>Access and modify the data from the $request object like you normally would in your own controller action</td>
	</tr>
	<tr>
		<td>Crud.afterSave</td>
		<td>$subject->id</td>
		<td>`$id` is only available if the save was successful. You can test also test on the `$subject->success` property if the save worked.</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered. This is also before the controllers own beforeRender callback</td>
	</tr>
</tbody>
</table>

## Edit CrudAction

The `edit` CrudAction will modify a record if the request is `HTTP PUT`, the data is valid and the ID that is part of the request exist in the database.

The source code can be found here: `Controller/Crud/Action/EditCrudAction.php`

### Events

<table>
<thead>
	<tr>
		<th>Event</th>
		<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforeSave</td>
		<td>N/A</td>
		<td>Access and modify the data from the $request object like you normally would in your own controller action</td>
	</tr>
	<tr>
		<td>Crud.afterSave</td>
		<td>$subject->id</td>
		<td>`$id` is only available if the save was successful. You can test also test on the `$subject->success` property if the save worked.</td>
	</tr>
	<tr>
		<td>Crud.beforeFind</td>
		<td>$subject->query</td>
		<td>Modify the $query array, same as the $queryParams in a behaviors beforeFind() or 2nd argument to any Model::find()</td>
	</tr>
	<tr>
		<td>Crud.recordNotFound</td>
		<td>N/A</td>
		<td>If beforeFind could not find a record this event is emitted</td>
	</tr>
	<tr>
		<td>Crud.afterFind</td>
		<td>N/A</td>
		<td>Modify the record found by find() and return it. The data is attached to the request->data object</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered. This is also before the controllers own beforeRender callback</td>
	</tr>
</tbody>
</table>

## View CrudAction

The `view` CrudAction will read a record from the database based on the ID that is part of the request.

The source code can be found here: `Controller/Crud/Action/ViewCrudAction.php`

### Events

<table>
<thead>
	<tr>
		<th>Event</th>
		<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforeFind</td>
		<td>$subject->query</td>
		<td>Modify the $query array, same as the $queryParams in a behaviors beforeFind() or 2nd argument to any Model::find()</td>
	</tr>
	<tr>
		<td>Crud.recordNotFound</td>
		<td>N/A</td>
		<td>If beforeFind could not find a record this event is emitted</td>
	</tr>
	<tr>
		<td>Crud.afterFind</td>
		<td>
			$subject->id
			$subject->item
		</td>
		<td>Modify the $subject->item property if you need to do any afterFind() operations</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>Invoked right before the view will be rendered. This is also before the controllers own beforeRender callback</td>
	</tr>
</tbody>
</table>

## Delete CrudAction

The `delete` CrudAction will delete a record if the request is `HTTP DELETE` or `HTTP POST` and the ID that is part of the request exist in the database.

The source code can be found here: `Controller/Crud/Action/DeleteCrudAction.php`

### Events

<table>
<thead>
	<tr>
		<th>Event</th>
		<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>N/A</td>
		<td>Initialize method</td>
	</tr>
	<tr>
		<td>Crud.beforeFind</td>
		<td>$subject->query</td>
		<td>Modify the $query array, same as the $queryParams in a behaviors beforeFind() or 2nd argument to any Model::find()</td>
	</tr>
	<tr>
		<td>Crud.recordNotFound</td>
		<td>N/A</td>
		<td>If beforeFind could not find a record this event is emitted</td>
	</tr>
	<tr>
		<td>Crud.beforeDelete</td>
		<td>N/A</td>
		<td>Stop the delete by redirecting away from the action or calling $event->stopPropagation()</td>
	</tr>
	<tr>
		<td>Crud.afterDelete</td>
		<td>N/A</td>
		<td>Executed after Model::delete() has called. You can check if the delete succeed or not in $subject->success</td>
	</tr>
</tbody>
</table>

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
	$this->Crud->action('index')->findMethod('published');

	// Get the current configuration
	$config = $this->Crud->action('index')->findMethod();

	// The admin_index() function will call find('unpublished') on your model
	$this->Crud->action('admin_unpublished')->findMethod('unpublished');

	// Get the current configuration
	$config = $this->Crud->action('admin_index')->findMethod();
}
```

### In the controller action

```php
// You don't have to provide the action name in 'action'
// since the default is the current action
public function index() {
	$this->Crud->action()->findMethod('published');

	// Get the current configuration
	$config = $this->Crud->action()->findMethod();

	return $this->Crud->executeAction();
}

public function admin_index() {
	$this->Crud->action()->findMethod('unpublished');

	// Get the current configuration
	$config = $this->Crud->action()->findMethod();

	return $this->Crud->executeAction();
}
```

## Change the view to be rendered

By default Crud renders a view with the same name as the controller action, but there are cases where you want to change this.

If you action is `admin_index` the `admin_index.ctp` view will be rendered by default.

### Using beforeFilter

```php
public function beforeFilter() {
	$this->Crud->action('index')->view('my_index');

	// Get the current configuration
	$config = $this->Crud->action('index')->view();

	$this->Crud->action('admin_index')->view('my_admin_index');

	// Get the current configuration
	$config = $this->Crud->action('admin_index')->view();
}
```

### In the controller action

```php
// You don't have to provide the action name in 'action'
// since the default is the current action

public function index() {
	$this->Crud->action()->view('my_index');

	// Get the current configuration
	$config = $this->Crud->action()->view();

	return $this->Crud->executeAction();
}

public function admin_index() {
	$this->Crud->action()->view('my_admin_index');

	// Get the current configuration
	$config = $this->Crud->action()->view();

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
	$this->Crud->action('delete')->enable();
	$this->Crud->action('admin_delete')->enable();
}
```

## Disable a Crud action on the fly

This can only be done in `beforeFilter` (or earlier in the request) since it's the last method called
before the actual controller action is executed.

Disabling a `CrudAction` automatically removes it from the Controller as if it was never defined in the `actions` array in the crud component configuration.

```php
// This can only be done in the beforeFilter

public function beforeFilter() {
	$this->Crud->action('delete')->disable();
	$this->Crud->action('admin_delete')->disable();
}
```

## Change CrudAction configuration settings on the fly

```php
// This can be done both in beforeFilter and the controller action
// All possible config keys can be found in the CrudAction classes (app/Plugin/Crud/Controller/Crud/Action)
public function beforeFilter() {
	$this->Crud->action('view')->config('validateId', 'uuid');

	// Get the current configuration
	$config = $this->Crud->action('view')->config('validateId');
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
	$this->Crud->action('delete')->config('secureDelete', false);

	// Get the current configuration
	$config = $this->Crud->action('delete')->config('secureDelete');
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
	$this->Crud->action('add')->config('relatedLists', array('User', 'Tags'));

	// Automatically executes find('list') on the User ($users) model
	$this->Crud->action('add')->config('relatedLists', array('User'));

	// Fetch related data from all model relations (default)
	$this->Crud->action('add')->config('relatedLists', true);

	// Don't fetch any related data
	$this->Crud->action('add')->config('relatedLists', false);

	// Get the current configuration
	$config = $this->Crud->action('add')->config('relatedLists');
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
	$this->Crud->action('add')->saveOptions(array('atomic' => false));
}
```
