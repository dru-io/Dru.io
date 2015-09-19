<?php

/**
 * @file
 * Drush integration for the Lightcase module.
 */

/**
 * Implements hook_drush_command().
 */
function lightcase_drush_command() {
  $items['lightcase-download'] = array(
    'callback' => 'lightcase_drush_download',
    'description' => dt('Downloads the required Lightcase jQuery plugin from Github.'),
    'arguments' => array(
      'path' => dt('Optional. The path to the download folder. If omitted, Drush will use the default location (sites/all/libraries/lightcase).'),
    ),
  );

  return $items;
}

/**
 * Callback to the lightcase-download Drush command.
 */
function lightcase_drush_download() {
  $args = func_get_args();

  $path = $args[0] ? $args[0] : drush_get_context('DRUSH_DRUPAL_ROOT') . '/sites/all/libraries/lightcase';
  $cmd = 'git clone https://github.com/cbopp-art/lightcase.git ' . $path;

  if (drush_shell_exec($cmd)) {
    drush_log(dt('Lightcase was download to !path.', array('!path' => $path)), 'success');
  }
  else {
    drush_log(dt('Drush was unable to download Lightcase to !path.', array('!path' => $path)) . "\n" . dt('Attempted command: !cmd.', array('!cmd' => $cmd)), 'error');
  }
}

/**
 * Implements drush_MODULE_post_COMMAND().
 */
function drush_lightcase_post_pm_enable() {
  $modules = func_get_args();

  if (in_array('lightcase', $modules)) {
    lightcase_drush_download();
  }
}
