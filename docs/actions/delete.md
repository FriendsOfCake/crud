---
title: Actions - Delete
layout: default
---

# Delete CrudAction

The `delete` CrudAction will delete a record if the request is `HTTP DELETE` or `HTTP POST` and the
ID that is part of the request exist in the database.

Relevant links:
	[PHP source code]({{ site.github_url }}/Controller/Crud/Action/DeleteCrudAction.php)
	|
	[API documentation]({{site.site}}/cakephp-crud/api/develop/class-DeleteCrudAction.html)


# Events

{% include actions/events.md %}

<table class="table">
<thead>
	<tr>
		<th>Event</th>
		<th>Subject modifiers</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	{% include actions/event/startup.md %}
	{% include actions/event/initialize.md %}
	{% include actions/event/before_find.md %}
	{% include actions/event/not_found.md %}
	{% include actions/event/before_delete.md %}
	{% include actions/event/after_delete.md %}
	{% include actions/event/before_redirect.md %}
</tbody>
</table>

# Configuration

{% include actions/configuration.md %}

<table class="table">
<thead>
	<tr>
		<th>Key</th>
		<th>Default value</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	{% include actions/config/enabled.md %}
	{% include actions/config/find_method.md %}
	{% include actions/config/secure_delete.md %}
</tbody>
</table>

# Methods

This is a list of the most relevant public methods in the Crud action class.

For a full list please see the [full API documentation]({{site.api_url}}/class-DeleteCrudAction.html)

<table class="table">
<thead>
	<tr>
		<th>Method</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	{% include actions/method/find_method.md %}
</tbody>
</table>
