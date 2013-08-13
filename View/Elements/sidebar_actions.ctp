<?php if (!empty($scaffoldSidebarActions)) : ?>
  <ul class="nav nav-list nav-list-vivid">
    <?php
      foreach ($scaffoldSidebarActions as $_item) {
        if ($_item['_type'] == 'header') {
          echo sprintf('<li class="nav-header">' . $_item['title'] . '</li>');
        } else {
          echo "\t\t<li>";
          echo $this->Html->link($_item['title'], $_item['url'], $_item['options'], $_item['confirmMessage']);
          echo " </li>\n";
        }
      }
    ?>
  </ul>
<?php endif; ?>
