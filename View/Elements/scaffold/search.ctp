<?php
if (empty($scaffoldFilters)) :
	return;
endif;
?>

<div class="filters-container">
	<div class="btn-filter-container">
		<button type="button" class="btn btn-link btn-activate-filters filters-collapsed" data-toggle="collapse" data-target=".filters">
			<?php echo __d('crud', 'Filter results'); ?>
		</button>
	</div>

	<div class="filters collapse">
		<?php
		echo $this->Form->create($modelClass);
		echo $this->Form->inputs($scaffoldFilters, null, array('fieldset' => false, 'legend' => false));
		echo $this->Form->submit(__d('crud', 'Filter'), array('name' => '_filter', 'class' => 'btn btn-primary btn-filter'));
		echo $this->Form->end();
		?>
	</div>
</div>
