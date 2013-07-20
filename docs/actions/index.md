---
title: Actions - Index
layout: default
---

# Index CrudAction

The `index` CrudAction [paginates](http://book.cakephp.org/2.0/en/core-libraries/components/pagination.html) over the primary model in the controller.

Relevant links: [PHP source code]({{ site.github_url }}/Controller/Crud/Action/IndexCrudAction.php) | [API documentation](http://cakephp.dk/cakephp-crud/develop/class-IndexCrudAction.html)

# Events

This is a list of events emitted by `IndexCrudAction`

<table class="table">
<thead>
	<tr>
		<th>Event</th>
		<th>Special subject properties</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td>Crud.init</td>
		<td>None</td>
		<td>Triggered when a `CrudAction` is created to handle a CakePHP request inside CrudComponent.</td>
	</tr>
	<tr>
		<td>Crud.beforePaginate</td>
		<td><code>paginator</code></td>
		<td>
				Triggered before <code>Controller::paginate()</code> is called.
				<br />
				The <code>paginator</code> property is a reference to the <code>PaginatorComponent</code>.
				<br />
				If you wish to modify the pagination settings, you should <strong>only</strong> modify <code>$event->subject->paginator->settings</code>.
				<br />
				Modifying <code>Controller::$paginate</code> will not have any effect during this callback.
		</td>
	</tr>
	<tr>
		<td>Crud.afterPaginate</td>
		<td><code>items</code></td>
		<td>
			This even is triggered right after the call to <code>Controller::paginate()</code>.
			<br />
			The <code>items</code> property contains all the database record found in the pagination call.
		</td>
	</tr>
	<tr>
		<td>Crud.beforeRender</td>
		<td>N/A</td>
		<td>
			Invoked right before the view will be rendered.
			<br />
			This is also before the controllers own beforeRender callback
		</td>
	</tr>
</tbody>
</table>

# Configuration

<table class="table">
<thead>
	<tr>
		<th>Key</th>
		<th>Default value</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td><code>enabled</code></td>
		<td><code>true</code></td>
		<td>If this action is enabled or not.</td>
	</tr>
	<tr>
		<td><code>view</code></td>
		<td><code>NULL</code></td>
		<td>The view file to render. If the value is <code>NULL</code> the normal CakePHP behavior will be used</td>
	</tr>
	<tr>
		<td><code>viewVar</code></td>
		<td><code>NULL</code></td>
		<td>The view property to store the pagination result as. If the value is <code>NULL</code> the plural name of the controller name will be used.</td>
	</tr>
	<tr>
		<td><code>serialize</code></td>
		<td><code>array()</code></td>
		<td>View vars to serialize if you use the <a href="{{site.url}}/docs/listeners/api.html">Crud API</a>. This property maps to `_serialize` in CakePHP</td>
	</tr>
</tbody>
</table>

# Methods

This is a list of the most relevant public methods in the Crud action class.

For a full list please see the [full API documentation]({{site.api_url}}/class-IndexCrudAction.html)

<table class="table">
<thead>
	<tr>
		<th>Method</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td><a href="{{site.api_url}}/class-IndexCrudAction.html#_viewVar">viewVar</a></td>
		<td>Get or set the <code>viewVar</code> configuration setting.</td>
	</tr>
</tbody>
</table>
