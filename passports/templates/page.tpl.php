<?php
/**
 * @file
 * Returns the HTML for a single Drupal page.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728148
 */
?>

<header class="header" id="header" role="banner">
  <div class="region-mobile-nav-container">
    <?php print render($page['mobile_nav']); ?>
  </div>

  <div class="header__inner clearfix">

    <?php if ($logo): ?>
        <a href="https://dfat.gov.au" class="header__logo" id="dfat-logo"><img src="<?php print base_path() . drupal_get_path('theme', 'passports');?>/images/dfat-logo.png" alt="Department of Foreign Affairs and Trade" class="header__logo-image" /></a>
        <img src="<?php print base_path() . drupal_get_path('theme', 'passports');?>/images/dfat-print-logo.png" alt="Department of Foreign Affairs and Trade" class="header__logo-image" id="dfat-logo-print" />
        <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" class="header__logo" id="apo-logo"><img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" class="header__logo-image" /></a>
  <?php endif; ?>

  <?php print render($page['header']); ?>
  </div>
</header>

<div class="region-nav-container">
  <?php print render($page['navigation']); ?>
</div>

<div id="page">

  <?php print render($page['highlighted']); ?>

  <?php if ($breadcrumb || $readspeaker_button): ?>
    <div class="breadcrumbs-wrapper">
      <?php print $breadcrumb; ?>
      <?php print $readspeaker_button; ?>
    </div>
  <?php endif; ?>

  <div id="main">

    <div id="content" class="column" role="main">

      <a href="#skip-link" id="skip-content" class="element-invisible">Go to top of page</a>

      <a id="main-content"></a>
      <?php print render($title_prefix); ?>
      <?php if ($title): ?>
        <h1 class="page__title title" id="page-title"><?php print $title; ?></h1>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <?php if ($messages): ?>
        <div class="message-wrapper">
          <?php print $messages; ?>
        </div>
      <?php endif; ?>
      <?php print render($tabs); ?>
      <?php print render($page['help']); ?>
      <?php if ($action_links): ?>
        <ul class="action-links"><?php print render($action_links); ?></ul>
      <?php endif; ?>
      <?php print render($page['content']); ?>
      <?php print $feed_icons; ?>
    </div>

    <?php
      // Render the sidebars to see if there's anything in them.
      // Since the out-of-the box GovCMS first sidebar is broken and we weren't
      // using it anyway, it has been removed for now. If it is required in
      // future we will have to add a working one from scratch.
      $sidebar_second = render($page['sidebar_second']);
    ?>

    <?php if ($sidebar_second): ?>
      <aside class="sidebars" role="complementary">
        <?php print $sidebar_second; ?>
      </aside>
    <?php endif; ?>

  </div>

  <div class="footer-container">
    <?php print render($page['footer']); ?>
  </div>

</div>

<?php print render($page['bottom']); ?>
