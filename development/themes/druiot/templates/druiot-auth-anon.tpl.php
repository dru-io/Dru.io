<?php
/**
 * @file
 * Profile markup for anonymous users.
 */
$default_picture = variable_get('user_picture_default', '');
?>
<div id="header-profile">
  <img src="/<?php print $default_picture; ?>" alt="Аватар"
       class="header-profile__picture"
       height="40" width="40">
  <a href="/user" class="header-profile__auth">
    <span class="label">Авторизация</span>
    <span class="icon"></span>
  </a>
</div>