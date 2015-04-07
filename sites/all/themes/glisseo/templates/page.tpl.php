<header id="header" role="banner">
  <?php if ($logo): ?>
    <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>"
       rel="home" id="logo">
      <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>"/>
    </a>
  <?php endif; ?>

  <?php if ($site_name || $site_slogan): ?>
    <div id="name-and-slogan">
      <?php if ($site_name): ?>
        <h2 id="site-name">
          <a href="<?php print $front_page; ?>"
             title="<?php print t('Home'); ?>"
             rel="home"><span><?php print $site_name; ?></span></a>
        </h2>
      <?php endif; ?>

      <?php if ($site_slogan): ?>
        <div id="site-slogan"><?php print $site_slogan; ?></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php print render($page['header']); ?>

  <?php print render($page['navigation']); ?>

</header>

  <?php
  ?>

  <?php
  // Print first sidebar, if it exist.
  $sidebar_first = render($page['sidebar_first']);
  if ($sidebar_first) {
    print $sidebar_first;
  } ?>

  <div id="content" role="main">
    <?php print render($page['highlighted']); ?>
    <?php print $breadcrumb; ?>
    <?php print render($title_prefix); ?>
    <?php if ($title): ?>
      <h1 id="page-title"><?php print $title; ?></h1>
    <?php endif; ?>
    <?php print render($title_suffix); ?>
    <?php print $messages; ?>
    <?php print render($tabs); ?>
    <?php print render($page['help']); ?>
    <?php if ($action_links): ?>
      <ul class="action-links"><?php print render($action_links); ?></ul>
    <?php endif; ?>
    <?php print render($page['content']); ?>
    <?php print $feed_icons; ?>
  </div>

  <?php
  // Print second sidebar, if it exist.
  $sidebar_second = render($page['sidebar_second']);
  if ($sidebar_second) {
    print $sidebar_second;
  } ?>

</main>

<?php print render($page['footer']); ?>

<?php print render($page['bottom']); ?>
