<?php

/**
 * @file
 * Theme functions for feeds.
 */

/**
 * Themes a status display for a source.
 */
function theme_feeds_feed_status($v) {
  $output = '<div class="info-box feeds-source-status">';
  $items = [];
  if ($v['progress_importing']) {
    $progress = number_format(100.0 * $v['progress_importing'], 0);
    $items[] = t('Importing - @progress % complete.', ['@progress' => $progress]);
  }
  if ($v['progress_clearing']) {
    $progress = number_format(100.0 * $v['progress_clearing'], 0);
    $items[] = t('Deleting items - @progress % complete.', ['@progress' => $progress]);
  }
  if (!count($items)) {
    if ($v['count']) {
      if ($v['imported']) {
        $items[] = t('Last import: @ago ago.', ['@ago' => \Drupal::service('date.formatter')->formatInterval(REQUEST_TIME - $v['imported'], 1)]);
      }
      $items[] = t('@count imported items total.', ['@count' => $v['count']]);
    }
    else {
      $items[] = t('No imported items.');
    }
  }
  $output .= theme('item_list', ['items' => $items]);
  $output .= '</div>';
  return $output;
}
