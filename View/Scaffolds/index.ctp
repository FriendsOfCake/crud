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
			foreach (${$pluralVar} as ${$singularVar}):
				echo '<tr>';
					foreach ($scaffoldFieldsData as $_field => $_options) {
						echo '<td>';
						echo $this->Crud->format(
							$_field,
							Hash::get(${$singularVar}, "{$modelClass}.{$_field}"),
							${$singularVar},
							$modelSchema,
							$associations
						);
						echo '</td>';
					}
				?>
				<td class="actions">
					<div class="btn-group">
						<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
							<?= __d('crud', 'Actions'); ?>
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<?php
							foreach ($scaffoldControllerActions['record'] as $_action) {
								echo "\t\t<li>";
								echo $this->Html->link(sprintf('%s %s', Inflector::humanize($_action), $singularHumanName), array(
									'action' => $_action, ${$singularVar}[$modelClass][$primaryKey]
								));
								echo " </li>\n";
							}
							?>
						</ul>
					</div>
					</td>
				</tr>
				<?php
				endforeach;
			?>
		</tbody>
	</table>
	<div class="paging paging-centered">
		<div>
			<?php
			echo $this->Paginator->prev(__d('crud', '<<'), array(), null, array('class' => 'prev disabled'));
			echo $this->Paginator->numbers(array('separator' => ''));
			echo $this->Paginator->next(__d('crud', '>>'), array(), null, array('class' => 'next disabled'));
			?>
		</div>
		<p>
			<?php
			echo $this->Paginator->counter(array(
				'format' => __d('crud', 'Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
			));
			?>
		</p>
	</div>
</div>
