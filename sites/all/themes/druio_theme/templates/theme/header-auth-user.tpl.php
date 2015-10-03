<?php
/**
 * @file
 * Template for registered users in header region.
 */
?>
<div class="header-auth-user">
  <a href="/user">
    <img src="<?php print $picture; ?>" alt="" width="40"
         height="40" class="picture">
    <span class="username"><?php print $username; ?></span>
  </a>
  <a href="/user/logout" class="logout" title="Выход">&nbsp;</a>
</div>
