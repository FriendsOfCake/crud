<tr>
	<td>Crud.beforeDelete</td>
	<td>
		<code>id</code>
	</td>
	<td>
		<code>id</code> The ID of the record that will be deleted.
		<br />
		Executed before <code>Model::delete()</code> is called.
		<br />
		Stop the delete by redirecting away from the action or calling <code>$event->stopPropagation()</code>.
	</td>
</tr>
