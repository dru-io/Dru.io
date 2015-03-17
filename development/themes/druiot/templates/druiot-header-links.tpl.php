<?php
/**
 * @vars
 * $has_new: TRUE if has new tracker updates. Not used for noew (w8 tracker).
 */
?>
<div id="header-links">
  <div class="header__tracker <?php $tracker_count ? print 'new' : FALSE; ?>"
    <?php $tracker_count ? print 'data-new-count="' . $tracker_count . '"' : FALSE; ?>>
    <a href="/tracker">Трекер</a></div>
  <a href="https://github.com/Niklan/Dru.io" class="header__github"
     rel="nofollow" target="_blank">Мы на GitHub</a>
</div>
