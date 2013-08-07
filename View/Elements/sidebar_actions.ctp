<?php if (isset($scaffoldSidebarActions) && $scaffoldSidebarActions !== false) : ?>
  <div class="actions">
  <?php if (is_array($scaffoldSidebarActions)) : ?>
    <h3><?php echo __d('cake', 'Actions'); ?></h3>
    <ul>
      <?php
        foreach ($scaffoldSidebarActions as $_item) {
          echo "\t\t<li>";
          if ($_item['type'] == 'link') {
            echo $this->Html->link($_item['title'], $_item['url'], $_item['options'], $_item['confirmMessage']);
          } else {
            echo $this->Form->postLink($_item['title'], $_item['url'], $_item['options'], $_item['confirmMessage']);
          }
          echo " </li>\n";
        }
      ?>
    </ul>
  <?php else : ?>
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
    <?php if (!empty($scaffoldRelatedActions)) : ?>
      <div class="related-actions">
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
  </div>
<?php endif; ?>
