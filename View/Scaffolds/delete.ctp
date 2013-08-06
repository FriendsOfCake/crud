<?php
  if (!empty(${$singularVar})) {
    $primaryKeyValue =  ${$singularVar}[$modelClass][$primaryKey];
  } else {
    $primaryKeyValue = $this->Form->value("{$modelClass}.{$primaryKey}");
  }
?>
<div class="<?php echo $pluralVar; ?> form">
  <?php echo $this->Form->create(null, array('type' => 'delete')); ?>
    <h2><?php echo Inflector::humanize($this->request->action) . ' ' .  $singularHumanName; ?></h2>
    <p><?php echo  __d('cake', 'Are you sure you want to delete # %s?', $primaryKeyValue); ?></p>
  <?php echo $this->Form->end(__d('crud', 'Delete')); ?>
</div>

<?php echo $this->element('sidebar_actions'); ?>
