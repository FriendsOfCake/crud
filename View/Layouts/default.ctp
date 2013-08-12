<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo $this->Html->charset(); ?>
  <title><?php echo $title_for_layout; ?> - <?php echo $scaffoldTitle ?></title>

  <?php
    echo $this->Html->meta('icon');

    echo $this->Html->css('Crud.style');
    echo $this->Html->script('Crud.jquery-1.10.2.min.js');
    echo $this->Html->script('Crud.bootstrap.min.js');

    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo $this->fetch('script');
  ?>
</head>
<body>
  <div class="wrap">
    <div class="navbar navbar-static-top navbar-inverse">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="#"><?php echo $scaffoldTitle; ?></a>
          <?php if (!empty($scaffoldNavigation)) : ?>
            <ul class="nav pull-right">
              <?php foreach ($scaffoldNavigation as $_item) : ?>
                <li <?php echo (Hash::get($_item, 'url.controller') == $this->request->controller) ? 'class="active"' : '' ?>>
                  <?php echo $this->Html->link($_item['title'], $_item['url'], $_item['options'], $_item['confirmMessage']); ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="page-title">
      <div class="container">
        <div class="row-fluid">
          <?php echo $this->Session->flash(); ?>
          <?php echo $this->fetch('pageTitle'); ?>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row-fluid">
        <div class="content span10">
          <?php echo $this->fetch('content'); ?>
        </div>
        <div class="sidebar-actions span2">
          <?php echo $this->element('sidebar_actions'); ?>
        </div>
      </div>
    </div>
    <div class="push"></div>
  </div>

  <div class="footer">
    <div class="container">
      <?php echo $this->Html->link(
          $this->Html->image('cake.power.gif', array('alt' => $title_for_layout, 'border' => '0')),
          'http://www.cakephp.org/',
          array('target' => '_blank', 'escape' => false)
        );
      ?>
    </div>
  </div>
  <?php
    echo $this->Html->scriptBlock("$('.filters').on('hide', function () {
      $('.btn-filter').removeClass('filters-open').addClass('filters-collapsed');
    });");
    echo $this->Html->scriptBlock("$('.filters').on('show', function () {
      $('.btn-filter').removeClass('filters-collapsed').addClass('filters-open');
    });");
  ?>
</body>
</html>
