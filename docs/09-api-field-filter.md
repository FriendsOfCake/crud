# Feed Filter Listener

Allow the requester to decide what fields and relations that should be
returned by providing a `fields` GET argument with a comma separated list of fields.

Example: `http://example.com/blogs.json?fields=Blog.id,Blog.name,Post.id,Post.name,Post.created`

# Setup

[This feature requires the Api listener to work](08-api.md)

## Attach it on the fly in your controller `beforeFilter`

This is recommended if you want to attach it only to specific controllers and actions

```php
<?php
class SamplesController extends AppController {

	public function beforeFilter() {
		// Also requires the API listener
		$this->Crud->config('listeners.api', 'Crud.Api');
		$this->Crud->config('listeners.field_filter', 'Crud.FieldFilter');

		parent::beforeFilter();
	}
}
?>
```

## Attach it using components array

This is recommended if you want to attach it to all controllers, application wide

```php
<?php
class SamplesController extends AppController {

	public $components = [
		'RequestHandler',
		'Crud.Crud' => [
			'actions' => [
				'index',
				'view',
			],
			'listeners' => [
				// Also requires the API listener
				'api' => 'Crud.Api',
				'field_filter', 'Crud.FieldFilter'
			]
		];

}
?>
```

# Configuration

## Allow no filter

By default when the listener is enabled, it will refuse requests without a `fields` key in the request

This behavior can be changed

```php
// Allow request without ?fields=
$this->Crud->listener('field_filter')->allowNoFilter(true);

// Reject requests without ?fields=
$this->Crud->listener('field_filter')->allowNoFilter(false);
```

## Whitelist fields

You can whitelist fields, if no whitelist exist for fields, all fields are allowed by default.

If whitelisting exist, only those fields will be allowed to be selected.

The fields must be in `Model.field` format

```php
$this->Crud->listener('field_filter')->whitelistFields(array('Model.id', 'Model.name', 'Model.created'));
```

## Blacklist fields

If no blacklist exist, no blacklisting is done

If blacklisting exist, the field will be removed from the field list if present in the `field` query argument

The fields must be in `Model.field` format

```php
$this->Crud->listener('field_filter')->blacklistFields(array('Model.password', 'Model.auth_token', 'Model.created'));
```

## Whitelist models

For any model relation automatically to be joined, it has to be whitelisted first.

If no whitelist exist, no relations will be added automatically

```php
$this->Crud->listener('field_filter')->whitelistModels(array('list', 'of', 'models'));
```

# Limitations

Related models is only supported in 1 level away from the primary model at this time. E.g. "Blog" => Auth, Tag, Posts
