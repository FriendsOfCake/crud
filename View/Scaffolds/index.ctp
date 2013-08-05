<div class="<?php echo $pluralVar; ?> index">
<h2><?php echo $pluralHumanName; ?></h2>
<?php if (!empty($scaffoldFilters)) : ?>
<div class="filter">
<?php
  echo $this->Form->create($modelClass, array(
    'url' => array_merge(array('action' => $this->request->params['action']), $this->params['pass'])
  ));
  foreach ($scaffoldFilters as $_field => $scaffoldFilter) {
    echo $this->Form->input($_field, $scaffoldFilter);
  }
  echo $this->Form->end(__d('crud', 'Filter'));
?>
</div>
<?php endif; ?>
<table cellpadding="0" cellspacing="0">
<tr>
<?php foreach ($scaffoldFields as $_field => $_options): ?>
  <th><?php echo $this->Paginator->sort($_field); ?></th>
<?php endforeach; ?>
  <th><?php echo __d('crud', 'Actions'); ?></th>
</tr>
<?php
foreach (${$pluralVar} as ${$singularVar}):
  echo '<tr>';
    foreach ($scaffoldFields as $_field => $_options) {
      $isKey = false;
      if (!empty($associations['belongsTo'])) {
        foreach ($associations['belongsTo'] as $_alias => $_details) {
          if ($_field === $_details['foreignKey']) {
            $isKey = true;
            echo '<td>' . $this->Html->link(${$singularVar}[$_alias][$_details['displayField']], array('controller' => $_details['controller'], 'action' => 'view', ${$singularVar}[$_alias][$_details['primaryKey']])) . '</td>';
            break;
          }
        }
      }
      if ($isKey !== true) {
        $type = Hash::get($modelSchema, "{$_field}.type");
        if ($type == 'boolean') {
          echo '<td>' . (!!${$singularVar}[$modelClass][$_field] ? 'Yes' : 'No') . '</td>';
        } elseif (in_array($type, array('datetime', 'date', 'timestamp'))) {
          echo '<td>' . $this->Time->timeAgoInWords(${$singularVar}[$modelClass][$_field]) . '</td>';
        } elseif ($type == 'time') {
          echo '<td>' . $this->Time->nice(${$singularVar}[$modelClass][$_field]) . '</td>';
        } else {
          echo '<td>' . h(${$singularVar}[$modelClass][$_field]) . '</td>';
        }
      }
    }

    echo '<td class="actions">';
    echo $this->Html->link(__d('crud', 'View'), array('action' => 'view', ${$singularVar}[$modelClass][$primaryKey]));
    echo ' ' . $this->Html->link(__d('crud', 'Edit'), array('action' => 'edit', ${$singularVar}[$modelClass][$primaryKey]));
    echo ' ' . $this->Form->postLink(
      __d('crud', 'Delete'),
      array('action' => 'delete', ${$singularVar}[$modelClass][$primaryKey]),
      null,
      __d('crud', 'Are you sure you want to delete # %s?', ${$singularVar}[$modelClass][$primaryKey])
    );
    echo '</td>';
  echo '</tr>';

endforeach;

?>
</table>
  <p><?php
  echo $this->Paginator->counter(array(
    'format' => __d('crud', 'Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
  ));
  ?></p>
  <div class="paging">
  <?php
    echo $this->Paginator->prev('< ' . __d('crud', 'previous'), array(), null, array('class' => 'prev disabled'));
    echo $this->Paginator->numbers(array('separator' => ''));
    echo $this->Paginator->next(__d('crud', 'next') .' >', array(), null, array('class' => 'next disabled'));
  ?>
  </div>
</div>

<?php echo $this->element('sidebar_actions'); ?>
