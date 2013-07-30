---
title: Unit Testing
layout: default
---

# Unit Testing with Crud

To ease with unit testing of Crud Listeners and Crud Actions, it's recommended
to use the proxy methods found in [CrudBaseObject](http://{{site.url}}/api/develop/class-CrudBaseObject.html).
<br />
<br />
These methods are much easier to mock than the full `CrudComponent` object.
<br />
<br />
They also allow you to just mock the methods you need for your specific test, rather than the big dependency nightmare the
CrudComponent can be in some cases.<br />

## Proxy methods

These methods are available in all `CrudAction` and `CrudListener` objects.

<table class="table">
<thead>
	<tr>
		<th>Proxy method</th>
		<th>Same as</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td><code>$this->_crud()</code></td>
		<td><code>$this->_container->crud</code></td>
		<td>Get the CrudComponent instance</td>
	</tr>
	<tr>
		<td><code>$this->_action($name)</code></td>
		<td><code>$this->_crud()->action($name)</code></td>
		<td>Get an CrudAction object by it's action name</td>
	</tr>
	<tr>
		<td><code>$this->_trigger($eventName, $data = [])</code></td>
		<td><code>$this->_crud()->trigger($eventName, $data = array())</code></td>
		<td>Trigger a <a href="{{site.url}}/docs/events.html">CrudEvent</a></td>
	</tr>
	<tr>
		<td><code>$this->_listener($name)</code></td>
		<td><code>$this->_crud()->_listener($name)</code></td>
		<td>Get a <a href="{{site.url}}/docs/listeners/intro.html">Crud Listener</a> by its name</td>
	</tr>
	<tr>
		<td><code>$this->_subject($additional = array())</code></td>
		<td><code>$this->_crud()->getSubject($additional = array())</code></td>
		<td>Create a <a href="{{site.url}}/docs/events.html#global_accessible_subject_properties">Crud event subject</a> - used in <code>$this->_trigger</code></td>
	</tr>
		<tr>
		<td><code>$this->_session()</code></td>
		<td><code>$this->_crud()->Session</code></td>
		<td>Get the Session Component instance</td>
	</tr>
	<tr>
		<td><code>$this->_controller()</code></td>
		<td><code>$this->_container->controller</code></td>
		<td>Get the controller for the current request</td>
	</tr>
	<tr>
		<td><code>$this->_request()</code></td>
		<td><code>$this->_container->request</code></td>
		<td>Get the current CakeRequest for this request</td>
	</tr>
	<tr>
		<td><code>$this->_model()</code></td>
		<td><code>$this->_container->model</code></td>
		<td>Get the model instance that is created from <code>Controller::$modelClass</code></td>
	</tr>
</tbody>
</table>
