<?php
/**
 * @vars
 * $has_new: TRUE if has new tracker updates. Not used for noew (w8 tracker).
 */
?>
<div id="header-links">
  <?php if ($tracker_count): ?>
    <div class="header__tracker"><a href="/tracker">Трекер <span><?php print render($tracker_count); ?></span></a></div>
  <?php endif ?>
  <a href="https://github.com/Niklan/Dru.io" class="header__github" rel="nofollow" target="_blank">Мы на GitHub</a>
</div>
