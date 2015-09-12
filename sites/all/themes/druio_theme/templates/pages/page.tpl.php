<header id="header" role="banner">
  <div class="pane">
    <div class="logo">
      <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>"
         rel="home">
        <img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>"
             width="170" height="106"/>
      </a>
    </div>

    <div class="content">
      <div class="top">
        <?php print $header_search_form; ?>
        <?php print $header_links; ?>
        <?php print $header_auth; ?>
      </div>
    </div>
  </div>
</header>

<!-- CONTENT BELOW IS UNDER CONSTRUCTION :) -->
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

<?php print render($page['navigation']); ?>
<main id="main">
  <?php if ($page['sidebar_first']): ?>
    <?php print render($page['sidebar_first']); ?>
  <?php endif; ?>

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

  <?php if ($page['sidebar_second']): ?>
    <?php print render($page['sidebar_second']); ?>
  <?php endif; ?>
</main>

<?php print render($page['footer']); ?>
