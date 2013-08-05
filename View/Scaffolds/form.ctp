<div class="<?php echo $pluralVar; ?> form">
<?php
  echo $this->Form->create();
  echo $this->Form->inputs($scaffoldFields);
  ?>
  <div class="submit">
  <?php
    echo $this->Form->submit(__d('cake', 'Save'), array('name' => '_save', 'div' => false));
    echo "&nbsp;";
    echo $this->Form->submit(__d('cake', 'Save and continue editing'), array('name' => '_edit', 'div' => false));
    echo "&nbsp;";
    echo $this->Form->submit(__d('cake', 'Save and add another'), array('name' => '_add', 'div' => false));
    echo "&nbsp;";
  ?>
  </div>
  <?php echo $this->Form->end(); ?>
  <?php
    if ($this->request->action !== 'add') {
      echo $this->Form->postLink(__d('cake', 'Delete %s', $singularHumanName), array('action' => 'delete', $this->data[$modelClass][$primaryKey]), null, __d('cake', 'Are you sure you want to delete # %s?', $this->data[$modelClass][$primaryKey]));
    }
  ?>
</div>

<?php echo $this->element('sidebar_actions'); ?>
