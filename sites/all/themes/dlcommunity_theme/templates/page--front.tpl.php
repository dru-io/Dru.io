<header id="header" role="banner">
  <div class="site-width gl-g">
    <div class="gl-s-1 gl-s-md-8-24 gl-s-lg-6-24 gl-p-1">
      <h2 id="site-name">
        <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>"
           rel="home">Drupalife<span>Community</span></a>
      </h2>
    </div>

    <div class="gl-s-1 gl-s-md-16-24 gl-s-lg-14-24 gl-p-1">
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
      <h2 class="welcome__title">Добро пожаловать на
        Drupalife<span>Community</span></h2>

      <h3 class="welcome__subtitle">Сообщество для Друпалеров</h3>
    </div>

    <div class="gl-s-1">
      <div class="front-search__wrapper">
        <form action="/search">
          <i class="front-search__icon icon-search search-pulse"></i>
          <input id="frontpage-search" name="s" maxlength="128"
                 class="front-search__input" type="text" autocomplete="off"
                 placeholder="Поиск по сообществу">
          <input type="submit" class="front-search__submit"
                 value="Найти">
        </form>
      </div>
    </div>
  </div>
</section>

<section id="mini-boxes" class="site-width gl-g">
  <div class="gl-s-1 gl-s-lg-1-3 gl-p-1 mini-box">
    <div class="mini-box__wrapper">
      <div class="icon-help mini-box__icon"></div>
      <h3 class="mini-box__title"><a href="/question">Вопрос — ответ</a></h3>

      <p class="mini-box__description">
        Коллективная помощь в решении вопросов. Если не знаешь — нужно лишь
        спросить.
      </p>
    </div>
  </div>

  <div class="gl-s-1 gl-s-lg-1-3 gl-p-1 mini-box">
    <div class="mini-box__wrapper">
      <div class="icon-doc-text mini-box__icon"></div>
      <h3 class="mini-box__title"><a href="/post">Публикации</a></h3>

      <p class="mini-box__description">
        Полезная и свежая информация. Будьте в курсе, и делитесь с другими.
      </p>
    </div>
  </div>

  <div class="gl-s-1 gl-s-lg-1-3 gl-p-1 mini-box">
    <div class="mini-box__wrapper">
      <div class="icon-plug mini-box__icon"></div>
      <h3 class="mini-box__title"><a href="/project">Проекты</a></h3>

      <p class="mini-box__description">
        Вся необходимая информация о проектах с Drupal.org и даже больше!
      </p>
    </div>
  </div>
</section>

<section id="front-materials">
  <div class="gl-g site-width">
    <div class="gl-s-1 gl-s-lg-1-2 gl-p-1 front-materials">
      <div class="front-materials__wrapper">
        <h2 class="front-materials__title">Последние вопросы</h2>

        <?php print views_embed_view('question_nodes', 'frontpage'); ?>
      </div>
    </div>

    <div class="gl-s-1 gl-s-lg-1-2 gl-p-1 front-materials">
      <div class="front-materials__wrapper">
        <h2 class="front-materials__title">Последние публикации</h2>

        <?php print views_embed_view('posts_node', 'frontpage'); ?>
      </div>
    </div>
  </div>
</section>

<footer id="footer" role="contentinfo">
  <div class="site-width gl-g">
    <?php print render($page['footer']); ?>

    <div class="gl-s-1 gl-p-1">
      <?php print date('Y', time()); ?> © Копирование с указанием ссылки на
      источник не запрещается.
    </div>
  </div>
</footer>