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

<section id="frontpage-latest">
  <div class="frontpage__questions">
    <?php print views_embed_view('question_nodes', 'frontpage'); ?>
  </div>
  <div class="frontpage__orders">
    <?php print views_embed_view('orders', 'block'); ?>
  </div>
  <div class="frontpage__posts">
    <?php print views_embed_view('posts_node', 'frontpage'); ?>
  </div>
  <div class="frontpage__events">
    <?php print views_embed_view('events', 'block_1'); ?>
  </div>
</section>
