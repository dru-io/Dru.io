<?php
/**
 * @vars
 * $has_new: TRUE if has new tracker updates. Not used for noew (w8 tracker).
 */
?>
<div id="header-links">
  <div class="header__tracker <?php druio_tracker_count() ? print 'new' : FALSE; ?>"
    <?php druio_tracker_count() ? print 'data-new-count="' . druio_tracker_count() . '"' : FALSE; ?>>
    <a href="/tracker"><span>Трекер</span></a></div>
  <a href="https://github.com/Niklan/Dru.io" class="header__github"
     rel="nofollow" target="_blank"><span>Мы на GitHub</span></a>
</div>
