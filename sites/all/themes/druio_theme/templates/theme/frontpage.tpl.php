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

<section id="drupal-sib">
  <img src="http://camp.drupalsib.ru/sites/all/themes/camp/img/hl-logo.png" alt="Drupal Sib 2015 logo" class="logo">
  <h3 class="title">DrupalCamp Siberia 2015</h3>
  <div class="dates">18-20 ДЕКАБРЯ</div>
  <div class="buttons">
    <a href="https://www.youtube.com/watch?v=Gr3k1H1C5eo" target="_blank" class="broadcast">Прямой эфир на YouTube</a>
    <a href="http://camp.drupalsib.ru/?utm_source=dru.io&utm_medium=text&utm_campaign=frontpage" target="_blank" class="website">Официальный сайт</a>
  </div>
</section>

<section id="frontpage-promoted">
  <?php print views_embed_view('promoted_content', 'block'); ?>
</section>

<section id="frontpage-latest">
  <?php print views_embed_view('question_nodes', 'frontpage'); ?>
  <?php print views_embed_view('orders', 'frontpage'); ?>
  <?php print views_embed_view('posts_node', 'frontpage'); ?>
  <?php print views_embed_view('events', 'frontpage'); ?>
</section>

<?php print theme('druio_pages_drupal_8_0_countown'); ?>