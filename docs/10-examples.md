# Examples

## Change the find() method

### Using beforeFilter

```php
public function beforeFilter() {
	// Same as Model::find('published')
	$this->Crud->getAction('index')->findMethod('published');

	// Same as Model::find('unpublished')
	$this->Crud->getAction('admin_unpublished')->findMethod('unpublished');
}
```

### In the controller action

```php
// You don't have to provide the action name in 'getAction'
// since the default is the current action
public function index() {
	$this->Crud->getAction()->findMethod('published');
	return $this->Crud->executeAction();
}

public function admin_index() {
	$this->Crud->getAction()->findMethod('unpublished');
	return $this->Crud->executeAction();
}
```

## Change the view to be rendered

### Using beforeFilter

```php
public function beforeFilter() {
	$this->Crud->getAction('index')->view('my_index');
	$this->Crud->getAction('admin_index')->view('my_admin_index');
}
```

### In the controller action

```php
// You don't have to provide the action name in 'getAction'
// since the default is the current action

public function index() {
	$this->Crud->getAction()->view('my_index');
	return $this->Crud->executeAction();
}

public function admin_index() {
	$this->Crud->getAction()->view('my_admin_index');
	return $this->Crud->executeAction();
}
```

## Enable a Crud action on the fly

```php
// This can only be done in the beforeFilter

public function beforeFilter() {
	$this->Crud->getAction('delete')->enable();
	$this->Crud->getAction('admin_delete')->enable();
}
```

## Disable a Crud action on the fly

```php
// This can only be done in the beforeFilter

public function beforeFilter() {
	$this->Crud->getAction('delete')->disable();
	$this->Crud->getAction('admin_delete')->disable();
}
```

## Change CrudAction config setting on the fly

```php
// This can be done both in beforeFilter and the controller action
// All possible config keys can be found in the CrudAction classes (app/Plugin/Crud/Controller/Crud/Action)
public function beforeFilter() {
	$this->Crud->getAction('view')->config('validateId', 'uuid');
}
```

## Disable secureDelete for delete() actions

```php
// Disabling this feature allow HTTP POST to execute delete() actions
// This can be changed in both beforeFilter and the controller action
public function beforeFilter() {
	$this->Crud->getAction('delete')->config('secureDelete', false);
}
```

## Configure related model data for add / edit views

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
}
```
