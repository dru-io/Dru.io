<?php

namespace Drupal\dru;

/**
 * @file
 * Contains \Drupal\synapse\Controller\Page.
 */

/**
 * Controller routines for page example routes.
 */
class MenuFaiconsReplace {

  /**
   * Set icon.
   */
  public static function replaceIcon($item) {
    if (strpos($item['title'], 'fa:')) {
      $title  = strstr($item['title'] . ':fa:', ':fa:', TRUE);
      $icon   = strstr($item['title'] . ':fa:', ':fa:');
      $icon   = str_replace(':fa:', '', $icon);
      $item['title'] = $title;
      $item['attributes']->setAttribute('icon', $icon);
      $item['attributes']->addClass('f');
    }
    if (!empty($item['below'])) {
      foreach ($item['below'] as $key => $item_bellow) {
        $item['below'][$key] = self::replaceIcon($item_bellow);
      }
    }
    return $item;
  }

}
