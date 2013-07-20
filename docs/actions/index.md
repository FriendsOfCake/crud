---
title: Actions - Index
layout: default
---

# Index CrudAction

The `index` CrudAction [paginates](http://book.cakephp.org/2.0/en/core-libraries/components/pagination.html) over the primary model in the controller.

Relevant links:
	[PHP source code]({{ site.github_url }}/Controller/Crud/Action/IndexCrudAction.php)
	|
	[API documentation](http://cakephp.dk/cakephp-crud/develop/class-IndexCrudAction.html)

# Events

This is a list of events emitted by `IndexCrudAction`

<table class="table">
<thead>
	<tr>
		<th>Event</th>
		<th>Subject properties</th>
		<th>Description</th>
	</tr>
</thead>
<tbody>
	{% include actions/event/init.md %}
	{% include actions/event/before_paginate.md %}
	{% include actions/event/after_paginate.md %}
	{% include actions/event/before_render.md %}
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
	{% include actions/config/view_var.md %}
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
	{% include actions/method/view.md %}
	{% include actions/method/view_var.md %}
	{% include actions/method/find_method.md %}
</tbody>
</table>
