<?php
/**
 * @file
 * druio_events.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function druio_events_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "captcha" && $api == "captcha") {
    return array("version" => "1");
  }
  if ($module == "ds" && $api == "ds") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function druio_events_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}
