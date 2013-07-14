---
title: Actions - Delete
layout: default
---

# Delete CrudAction

The `delete` CrudAction will delete a record if the request is `HTTP DELETE` or `HTTP POST` and the
ID that is part of the request exist in the database.

The source code can be found here: [Controller/Crud/Action/DeleteCrudAction.php]({{ site.github_url }}/Controller/Crud/Action/DeleteCrudAction.php)

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
		<td>Crud.beforeDelete</td>
		<td>N/A</td>
		<td>Stop the delete by redirecting away from the action or calling $event->stopPropagation()</td>
	</tr>
	<tr>
		<td>Crud.afterDelete</td>
		<td>N/A</td>
		<td>Executed after Model::delete() has called. You can check if the delete succeed or not in $subject->success</td>
	</tr>
</tbody>
</table>
