<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <?php echo $this->Html->charset(); ?>
  <title><?php echo $title_for_layout; ?></title>

  <?php
    echo $this->Html->meta('icon');

    echo $this->Html->css('Crud.cake.generic');

    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo $this->fetch('script');
  ?>
</head>
<body>
  <div id="container">
    <div id="header">
      <h1><?php echo $scaffoldTitle; ?> - <?php echo $title_for_layout; ?></h1>
      <?php if (!empty($scaffoldNavigation)) : ?>
        <div class="menu">
          <ul>
            <?php foreach ($scaffoldNavigation as $_item) : ?>
              <li <?php echo (Hash::get($_item, 'url.controller') == $this->request->controller) ? 'class="active"' : '' ?>>
                <?php
                  if ($_item['type'] == 'link') {
                    echo $this->Html->link($_item['title'], $_item['url'], $_item['options'], $_item['confirmMessage']);
                  } else {
                    echo $this->Form->postLink($_item['title'], $_item['url'], $_item['options'], $_item['confirmMessage']);
                  }
                ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
    <div id="content">

      <?php echo $this->Session->flash(); ?>

      <?php echo $this->fetch('content'); ?>
    </div>
    <div id="footer">
      <?php echo $this->Html->link(
          $this->Html->image('cake.power.gif', array('alt' => $title_for_layout, 'border' => '0')),
          'http://www.cakephp.org/',
          array('target' => '_blank', 'escape' => false)
        );
      ?>
    </div>
  </div>
</body>
</html>
