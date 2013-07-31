<?php if (isset($sidebarActions) && $sidebarActions !== false) : ?>
  <?php if (is_array($sidebarActions)) : ?>
    <div class="actions">
      <h3><?php echo __d('cake', 'Actions'); ?></h3>
      <ul>
      <?php
        foreach ($sidebarActions as $sidebarAction) {
          echo "\t\t<li>";
          if ($sidebarAction['type'] == 'post') {
            echo $this->Form->postLink($sidebarAction['title'], $sidebarAction['url'], $sidebarAction['options'], $sidebarAction['confirmMessage']);
          } else {
            echo $this->Html->link($sidebarAction['title'], $sidebarAction['url'], $sidebarAction['options'], $sidebarAction['confirmMessage']);
          }
          echo " </li>\n";
        }
      ?>
      </ul>
    </div>
  <?php else : ?>
    <div class="actions">
      <h3><?php echo __d('cake', 'Actions'); ?></h3>
      <ul>
      <?php
        if (!empty(${$singularVar})) {
          $primaryKeyValue =  ${$singularVar}[$modelClass][$primaryKey];
        } else {
          $primaryKeyValue = $this->Form->value("{$modelClass}.{$primaryKey}");
        }

        if (in_array($this->request->action, array('delete', 'view'))) {
          echo "\t\t<li>";
          echo $this->Html->link(__d('cake', 'Edit %s', $singularHumanName),   array('action' => 'edit', $primaryKeyValue));
          echo " </li>\n";
        }

        if (in_array($this->request->action, array('edit', 'view'))) {
          echo "\t\t<li>";
          echo $this->Form->postLink(__d('cake', 'Delete %s', $singularHumanName), array('action' => 'delete', $primaryKeyValue), null, __d('cake', 'Are you sure you want to delete # %s?', $primaryKeyValue));
          echo " </li>\n";
        }

        if ($this->request->action != 'index') {
          echo "\t\t<li>";
          echo $this->Html->link(__d('cake', 'List %s', $pluralHumanName), array('action' => 'index'));
          echo " </li>\n";
        }

        echo "\t\t<li>";
        echo $this->Html->link(__d('cake', 'New %s', $singularHumanName), array('action' => 'add'));
        echo " </li>\n";
      ?>
      </ul>
    </div>
    <div class="actions related-actions">
      <h3><?php echo __d('cake', 'Related Actions'); ?></h3>
      <ul>
        <?php
        $done = array();
        foreach ($associations as $_type => $_data) {
          foreach ($_data as $_alias => $_details) {
            if ($_details['controller'] != $this->name && !in_array($_details['controller'], $done)) {
              echo "\t\t<li>";
              echo $this->Html->link(
                __d('cake', 'List %s', Inflector::humanize($_details['controller'])),
                array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'index')
              );
              echo "</li>\n";
              echo "\t\t<li>";
              echo $this->Html->link(
                __d('cake', 'New %s', Inflector::humanize(Inflector::underscore($_alias))),
                array('plugin' => $_details['plugin'], 'controller' => $_details['controller'], 'action' => 'add')
              );
              echo "</li>\n";
              $done[] = $_details['controller'];
            }
          }
        }
      ?>
      </ul>
    </div>
  <?php endif; ?>
<?php endif; ?>
