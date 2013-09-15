<?php
foreach ($scaffoldFieldsData as $_field => $_options) :
	?>
	<td>
		<?php
		echo $this->Crud->format(
			$_field,
			Hash::get($_singularVar, "{$modelClass}.{$_field}"),
			$_singularVar,
			$modelSchema,
			$associations
		);
		?>
	</td>
	<?php
endforeach;
