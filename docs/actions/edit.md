---
title: Actions - Edit
layout: default
---

# Edit CrudAction

The `edit` CrudAction will update an existing record if the request is `HTTP POST` or `HTTP PUT` and the data validates.

Relevant links: [PHP source code]({{ site.github_url }}/Controller/Crud/Action/EditCrudAction.php) | [API documentation](http://cakephp.dk/cakephp-crud/develop/class-EditCrudAction.html)

# Events

{% include actions/events.md %}

<table class="table">
<thead>
	<tr>
		<th>Event</th>
		<th>Subject properties</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	{% include actions/event/startup.md %}
	{% include actions/event/initialize.md %}
	{% include actions/event/before_save.md %}
	{% include actions/event/after_save.md %}
	{% include actions/event/before_find.md %}
	{% include actions/event/after_find.md %}
	{% include actions/event/not_found.md %}
	{% include actions/event/before_render.md %}
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
	{% include actions/config/view.md %}
	{% include actions/config/related_models.md %}
	{% include actions/config/save_options.md %}
	{% include actions/config/serialize.md %}
</tbody>
</table>

# Methods

This is a list of the most relevant public methods in the Crud action class.

For a full list please see the [full API documentation]({{site.api_url}}/class-AddCrudAction.html)

<table class="table">
<thead>
	<tr>
		<th>Method</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	{% include actions/listener/related_models.md %}
	{% include actions/method/find_method.md %}
	{% include actions/method/view.md %}
	{% include actions/method/save_options.md %}
</tbody>
</table>
