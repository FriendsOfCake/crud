# Api Listener

This listener allows you to easily create a JSON or XML Api build on top of Crud.

## Setup

The Api listener depends on the `RequestHandler` to be loaded __before__ `Crud`

[Please also see the CakePHP documentation on JSON and XML views](http://book.cakephp.org/2.0/en/views/json-and-xml-views.html#enabling-data-views-in-your-application)

### Router

You need to tell the `Router` to parse extensions else it won't be able to process and render the `json` and `xml` URL extension

```php
// app/Config/routes.php
Router::parseExtensions('json', 'xml');
```

#### Attach it on the fly in your controller `beforeFilter`

This is recommended if you want to attach it only to specific controllers and actions

```php
<?php
class SamplesController extends AppController {

	public function beforeFilter() {
		$this->Crud->config('listeners.api', 'Crud.Api');

		parent::beforeFilter();
	}
}
?>
```

#### Attach it using components array

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
				'api' => 'Crud.Api'
			]
		];

}
?>
```

# New CakeRequest detectors

The Api Listener creates 3 new detectors in your `CakeRequest` object.

## is('json')

Checks if the extension of the request is '.json' or if the requester accepts json as part of the `HTTP accepts` header

## is('xml')

Checks if the extension of the request is '.xml' or if the requester accepts XML as part of the `HTTP accepts` header

## is('api')

Checking if the request is either `is('json')` or `is('xml')`

## Default behavior

If the current request doesn't evaluate `is('api')` to true, the listener won't do anything at all.
All it's callbacks will simply return NULL and don't get in your way.

# Exception handler

The Api listener overrides the `Exception.renderer` for `api` requests, so in case of an error, a standardized error will be returned, in either `json` or `xml` - according to the API request type.

# Request type enforcing

The Api listener will try to enforce some best practices on how an API should behave.

For a request to `index`, `view`, `admin_index` or `admin_view` the HTTP request type __must__ be `HTTP GET` - else an `MethodNotAllowed` exception will be raised.

For a request to `add`, `admin_add` the HTTP request type __must__ be `HTTP POST` - else an `MethodNotAllowed` exception will be raised.

For a request to `edit`, `admin_edit` the HTTP request type __must__ be `HTTP PUT` - else an `MethodNotAllowed` exception will be raised.

For a request to `delete`, `admin_delete` the HTTP request type __must__ be `HTTP DELETE` - else an `MethodNotAllowed` exception will be raised.

# Response format

The default response format for both XML and JSON is two root keys, `success` and `data`

It's possible to add your root keys simply by [expanding on the cakephp](http://book.cakephp.org/2.0/en/views/json-and-xml-views.html#enabling-data-views-in-your-application) `_serialize` key

## JSON

```json
{
	"success": true,
	"data": {}
}
```

## XML

```xml
<response>
	<success>1</success>
	<data></data>
</response>
```

## Exception response format

The `data.exception` key is only returned if `debug` is > 0

The `data.queryLog` key is only included if `debug` is > 1

### JSON

```json
{
	"success": false,
	"data": {
		"code": 500,
		"url": "/some/url.json",
		"name": "Some exception message",
		"exception": {
			"class": "CakeException",
			"code": 500,
			"message": "Some exception message",
			"trace": []
		},
		"queryLog": [ ]
	}
}
```

### XML

```xml
<response>
	<success>0</success>
	<data>
		<code>500</code>
		<url>/some/url.json</url>
		<name>Some exception message</name>
		<exception>
			<class>CakeException</class>
			<code>500</code>
			<message>Some exception message</message>
			<trace></trace>
			<trace></trace>
		</exception>
		<queryLog/>
	</data>
</response>
```

## HTTP POST / PUT (add / edit)

`success` is based on the `event->subject->success` parameter from `Add` or `Edit` action

If `success` is `false` a HTTP response code of `400` will be returned, and the `data` property will be the list of validation errors from the model.

If `success` is `true` a HTTP response code of `201` will be returned if the model item was __created__ else a `301` response code will be used.

A success will always include a HTTP `Location` header to the `view` action with the existing or newly created id of the record

## HTTP DELETE (delete)

`success` is based on the `event->subject->success` parameter from the `delete`action

`data` will always be `null`

No special HTTP codes is sent

## Not Found (view / edit / delete)

In case a `ìd` is provided to a crud action and the id does not exist in the database, a `404` `NotFoundException` will be thrown

## Invalid id (view / edit / delete)

In case a `ìd` is provided to a crud action and the id is not valid according to the database type a `500` `BadRequestException` will be thrown
