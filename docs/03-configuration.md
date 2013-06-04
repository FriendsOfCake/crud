## Configuration

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
