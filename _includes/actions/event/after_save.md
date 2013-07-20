<tr>
	<td>Crud.afterSave</td>
	<td>
		<code>success</code>
		<br />
		<code>id</code>
		<br />
		<code>created</code>
	</td>
	<td>
		This event is triggered right after the call to <code>Model::saveAll()</code>.
		<br />
		<code>success</code> indicates whether or not the <code>Model::saveAll()</code> call succeed or not.
		<br />
		<code>id</code> is only available if the save was successful.
		<br />
		<code>created</code> whether the record was created or modified.
	</td>
</tr>
