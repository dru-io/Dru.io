<?php
/**
 * @file
 * Template for registered users in header region.
 */
?>
<div class="header-auth-user">
  <a href="/user">
    <img src="<?php print $picture; ?>" alt="" width="37"
         height="37" class="picture">
    <span class="username"><?php print $username; ?></span>
  </a>
  <a href="/user/logout" class="logout" title="Выход">&nbsp;</a>
</div>
