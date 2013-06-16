---
title: Actions - Edit
layout: default
---

# Edit CrudAction

The `edit` CrudAction will modify a record if the request is `HTTP PUT`, the data is valid and the ID that is part of the request exist in the database.

The source code can be found here: [Controller/Crud/Action/EditCrudAction.php]({{ site.github_url }}/Controller/Crud/Action/EditCrudAction.php)

# Events

<table class="table">
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
