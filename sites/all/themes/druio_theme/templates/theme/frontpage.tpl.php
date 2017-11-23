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

<section id="drupalcamp-ug-2017">
  <div class="camp-pane">
    <div class="logo-wrapper">
      <img src="/sites/default/files/custom/drupal-ug/yug-logo-bigg.png" alt="DrupalCamp Краснодар логотип." class="logo">
    </div>

    <div class="content-wrapper">
      <div class="title">DrupalCamp Краснодар 2017</div>

      <div class="description">
        <p>DrupalCamp Краснодар 2017 — значимое событие для российского Друпал-сообщества — это выражение нашей приверженности к сотрудничеству и взаимному обогащению знаниями и опытом. Мы привлечем множество интересных людей, которые наполнят Краснодар энергией конструктивного общения, познавательными кейсами и конечно же Друпалом.</p>

        <p>DrupalCamp — важная часть жизни Друпал-сообщества, способствующая его сплочению, привлекающая внимание IT-сообщества к Друпалу и общества в целом к разработке приложений и сайтов, а также к смежным сферам.</p>

        <p>Разработчики, веб-мастера, дизайнеры, менеджеры проектов, владельцы бизнеса и работодатели — внимание большого количества людей будет привлечено нашим событием.</p>

        <p>Интересно будет всем, ждем вас 16 декабря в Краснодаре!</p>
      </div>

      <div class="buttons">
        <a href="http://2017.drupalyug.ru/?utm_source=druio&utm_medium=banner&utm_campaign=frontpage" class="website" target="_blank">Официальный сайт конференции</a>
        <span class="youtube-button">Видео приглашение</span>
      </div>
    </div>
  </div>

  <div class="video-pane">
    <div class="close"></div>
    <div class="video"></div>
  </div>
</section>

<section id="frontpage-latest">
  <?php print views_embed_view('question_nodes', 'frontpage'); ?>
  <?php print views_embed_view('orders', 'frontpage'); ?>
  <?php print views_embed_view('posts_node', 'frontpage'); ?>
  <?php print views_embed_view('events', 'frontpage'); ?>
</section>

<?php print theme('druio_pages_drupal_8_0_countown'); ?>