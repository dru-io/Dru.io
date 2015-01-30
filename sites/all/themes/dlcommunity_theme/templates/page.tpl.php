<header id="header" role="banner">
  <div class="site-width gl-g">
    <div class="gl-s-1 gl-s-md-8-24 gl-s-lg-6-24 gl-p-1">
      <h2 id="site-name">
        <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>"
           rel="home">Dru<span>.io</span></a>
      </h2>
      <h3 id="site-slogan"><?php print $site_slogan; ?></h3>
    </div>

    <div class="gl-s-1 gl-s-16-24 gl-s-lg-14-24 gl-p-1">
      <?php print render($page['navigation']); ?>
    </div>

    <div class="gl-s-1 gl-s-lg-4-24 gl-p-1">
      <?php print dlcommunity_theme_get_auth_buttons(); ?>
    </div>
  </div>
</header>

<section id="header-secondary">
  <div class="site-width gl-g gl-p-1">
    <div class="gl-s-1">
      <?php print render($title_prefix); ?>
      <h1 id="page-title"><?php print $title; ?></h1>
      <?php print render($title_suffix); ?>
    </div>
  </div>
</section>

<main id="main">
  <div class="main-content__wrapper site-width gl-g">
    <div id="content" <?php print $content_classes; ?>>
      <div <?php print $breadcrumb_classes; ?>>
        <?php print $breadcrumb; ?>
      </div>
      <?php print render($page['highlighted']); ?>
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
    if ($sidebar_first) {
      print $sidebar_first;
    } ?>
  </div>
</main>

<footer id="footer" role="contentinfo">
  <div class="site-width gl-g">
    <?php print render($page['footer']); ?>

    <div class="gl-s-1 gl-p-1">
      <?php print date('Y', time()); ?> © Копирование с указанием ссылки на источник не запрещается.
    </div>
  </div>
</footer>