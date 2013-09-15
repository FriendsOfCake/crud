<?php
$this->start('pageTitle');
	?>
	<h2><?php echo $scaffoldPageTitle; ?></h2>
	<?php
$this->end();
?>

<div class="<?php echo $pluralVar; ?> index scaffold-view">
	<?= $this->element('scaffold/search'); ?>

	<table cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<?php
				foreach ($scaffoldFieldsData as $_field => $_options):
					?>
					<th><?php echo $this->Paginator->sort($_field); ?></th>
					<?php
				endforeach;
				?>
				<th><?php echo __d('crud', 'Actions'); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach (${$pluralVar} as $_singularVar) :
				?>
				<tr>
					<?= $this->element('scaffold/table_columns', compact('_singularVar')); ?>
					<td class="actions">
						<?= $this->element('scaffold/table_actions', compact('_field', '_options', '_singularVar')); ?>
					</td>
				</tr>
				<?php
				endforeach;
			?>
		</tbody>
	</table>

	<?= $this->element('scaffold/pagination'); ?>
</div>
