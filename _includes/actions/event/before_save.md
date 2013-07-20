<tr>
	<td>Crud.beforeSave</td>
	<td>
		<code>id</code> ("edit" only)
	</td>
	<td>
		Called right before calling <code>Model::saveAll</code>.
		<br />
		<code>id</code> The ID of the record that will be saved
		<br />
		Access and modify the data from the <code>$request</code> object like you normally would in your own controller action
	</td>
</tr>
