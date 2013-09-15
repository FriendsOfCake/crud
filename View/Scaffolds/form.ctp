<?php $this->start('pageTitle'); ?>
<h2><?php echo $scaffoldPageTitle; ?></h2>
<?php $this->end(); ?>

<div class="<?php echo $pluralVar; ?> form scaffold-view">
	<?php echo $this->Form->create(); ?>
	<?php echo $this->Crud->redirectUrl(); ?>
	<?php echo $this->Form->inputs($scaffoldFieldsData, null, array('legend' => false)); ?>

	<div class="submit">
		<?php
		echo $this->Form->submit(__d('crud', 'Save'), array('name' => '_save', 'div' => false, 'class' => 'btn btn-save'));
		echo "&nbsp;";
		echo $this->Form->submit(__d('crud', 'Save and continue editing'), array('name' => '_edit', 'div' => false, 'class' => 'btn btn-alt-option btn-save-continue'));
		echo "&nbsp;";
		echo $this->Form->submit(__d('crud', 'Cancel'), array('name' => '_cancel', 'div' => false, 'class' => 'btn btn-alt-option btn-save-cancel'));
		echo "&nbsp;";
		?>
	</div>
	<?php echo $this->Form->end(); ?>
</div>
