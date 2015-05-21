<header id="header" role="banner">
  <div class="header__wrapper">
    <section class="header__logo">
      <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>"
         rel="home">
        <img src="<?php print $logo; ?>" alt="<?php print $site_name; ?>"
             class="logo">
      </a>
    </section>

    <section class="header__content">
      <div class="header__firstline">
        <?php print $header_search_form; ?>
        <div id="header-links">
          <div class="header__tracker <?php print _druio_messages_status(); ?>" <?php print 'data-new-count="' . druio_tracker_count() . '">'; ?>
          <a href="/tracker"><span>Трекер</span></a></div>
        <a href="https://github.com/Niklan/Dru.io" class="header__github"
           rel="nofollow" target="_blank"><span>Мы на GitHub</span></a>
      </div>
        <?php print $header_profile; ?>
      </div>

      <?php print render($page['navigation']); ?>
    </section>
  </div>
</header>

<main id="main">
  <?php if (!$is_front): ?>
    <section id="content" role="main">
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
    </section>

    <?php
    if ($sidebar_right) {
      print $sidebar_right;
    } ?>
  <?php else: ?>
    <section id="content" role="main">
      <?php print theme('druiot_frontpage_content'); ?>
    </section>
  <?php endif; ?>
</main>

<footer id="footer">
  &copy; Dru.io Копирование разрешено при указании обратной гиперссылки.
</footer>