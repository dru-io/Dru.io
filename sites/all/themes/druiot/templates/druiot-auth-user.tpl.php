<?php
/**
 * @file
 * Profile markup for users.
 */
$full_user = user_load($user->uid);
$picture = $user->picture ? image_style_url('avatar_thumb', $full_user->picture->uri) : '/' . variable_get('user_picture_default', '');
?>
<div id="header-profile">
  <img src="<?php print $picture; ?>" alt="Аватар"
       class="header-profile__picture"
       height="40" width="40">
  <a href="/user" class="header-profile__profile">
    Профиль
  </a>
  <a href="/user/logout" class="header-profile__logout"></a>
</div>
