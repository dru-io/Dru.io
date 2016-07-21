<?php
/**
 * @file
 * Template for anonymous user in header region.
 */

if($_SERVER["REQUEST_URI"]){
    $destination = '?destination='.$_SERVER["REQUEST_URI"];
}
else{
    $destination = '';
}
?>
<div class="header-auth-anon">
  <a href="/user/register" class="link sign-up">Регистрация</a>
  <a href="/user<?php print $destination?>" class="link sign-in">Вход</a>
</div>
