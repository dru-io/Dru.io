<?php
global $user;
// Disable author name.
unset($form['author']);
// Default user picture.
$user_picture = '/' . variable_get('user_picture_default');
// If user have own avatar.
if (isset($user->picture) && $user->picture > 0) {
  $picture = file_load($user->picture);
  $user_picture = file_create_url($picture->uri);
}
$user_name = isset($user->name) ? $user->name : variable_get('anonymous', 'Anonymous');
?>
<div class="grid-1-6 left">
  <div class="user-name">
    <?php print $user_name; ?>
  </div>
  <img src="<?php print $user_picture; ?>">
</div>
<div class="grid-5-6 left">
  <?php print drupal_render_children($form); ?>
</div>