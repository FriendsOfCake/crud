<?php if (!empty($scaffoldRelatedActions) || (isset($scaffoldSidebarActions) && $scaffoldSidebarActions !== false)) : ?>
  <ul class="nav nav-list nav-list-vivid">
  <?php if (is_array($scaffoldSidebarActions)) : ?>
    <li class="nav-header"><?php echo __d('cake', 'Actions'); ?></li>
    <?php
      foreach ($scaffoldSidebarActions as $_item) {
        echo "\t\t<li>";
        echo $this->Html->link($_item['title'], $_item['url'], $_item['options'], $_item['confirmMessage']);
        echo " </li>\n";
      }
    ?>
    <?php else : ?>
    <li class="nav-header"><?php echo __d('cake', 'Actions'); ?></li>
      <?php
        if (!empty(${$singularVar})) {
          $primaryKeyValue =  ${$singularVar}[$modelClass][$primaryKey];
        } else {
          $primaryKeyValue = $this->Form->value("{$modelClass}.{$primaryKey}");
        }

        foreach ($scaffoldControllerActions['model'] as $_action) {
          if ($this->request->action != $_action) {
            echo "\t\t<li>";
            echo $this->Html->link(sprintf('%s %s', Inflector::humanize($_action), $pluralHumanName), array(
              'action' => $_action
            ));
            echo " </li>\n";
          }
        }
        if (!in_array($this->request->action, $scaffoldControllerActions['model'])) {
          foreach ($scaffoldControllerActions['record'] as $_action) {
            if ($this->request->action != $_action) {
              echo "\t\t<li>";
              echo $this->Html->link(sprintf('%s %s', Inflector::humanize($_action), $singularHumanName), array(
                'action' => $_action, $primaryKeyValue
              ));
              echo " </li>\n";
            }
          }
        }
      ?>
    <?php if (!empty($scaffoldRelatedActions)) : ?>
        <li class="nav-header"><?php echo __d('cake', 'Related Actions'); ?></li>
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
    <?php endif; ?>
  <?php endif; ?>
  </ul>
<?php endif; ?>
