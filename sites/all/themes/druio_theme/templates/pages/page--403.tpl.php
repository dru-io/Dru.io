<section class="content">
  <h3 class="code">403</h3>
  <h4 class="status">Access denied</h4>
  <?php if ($is_anon): ?>
    <a href="/user" class="auth">Авторизация</a> <a href="/user/register" class="register">Регистрация</a>
  <?php endif; ?>
  <a href="<?php print $front_page; ?>" class="home"><?php print t('Home'); ?></a>
</section>