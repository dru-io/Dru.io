<?php if (user_is_anonymous()): ?>
<section id="frontpage-top">
  <div class="left">
    <h2 class="welcome">Добро пожаловать на Dru.io</h2>
    <h1 class="community">Русскоязычное Drupal сообщество</h1>

    <p>
      Drupal — система управления содержимым, используемая также как каркас для веб-приложений (CMF), написанная на языке PHP и использующая в качестве хранилища данных реляционную базу данных (поддерживаются MySQL, PostgreSQL и другие). Drupal является свободным программным обеспечением, защищённым лицензией GPL, и развивается усилиями энтузиастов со всего мира.
    </p>

    <p>
      Dru.io — место, где вы можете задать интересующие вас вопросы по Drupal, пообщаться с профессиональными разработчиками, получить бесплатную поддержку, узнать самую свежую информацию и стать частью нашего дружного сообщества.
    </p>
  </div>
  <div class="right"></div>
</section>
<?php endif; ?>

<section id="frontpage-promoted">
  <?php print views_embed_view('promoted_content', 'block'); ?>
</section>

<section id="drupalug-2016">
  <a href="http://2016.drupalyug.ru?from=dru.io" rel="nofollow" target="_blank" class="logo">
    <img src="/sites/all/themes/druio_theme/images/drupalcampug-logo.png" alt="Друпал Юг 2016 логотип">
  </a>

  <div class="event-name">
    <div class="name">Краснодар 2016</div>
    <a href="http://2016.drupalyug.ru?from=dru.io" rel="nofollow" target="_blank" class="web">2016.drupalyug.ru</a>
  </div>

  <div class="event-date">
    <div class="period">9 - 11 сентября</div>
    <div class="place">Кубанский государственный университет</div>
  </div>
</section>

<section id="frontpage-latest">
  <?php print views_embed_view('question_nodes', 'frontpage'); ?>
  <?php print views_embed_view('orders', 'frontpage'); ?>
  <?php print views_embed_view('posts_node', 'frontpage'); ?>
  <?php print views_embed_view('events', 'frontpage'); ?>
</section>

<?php print theme('druio_pages_drupal_8_0_countown'); ?>