<?php

/**
 * Implements drush_hook_COMMAND_validate().
 * 
 * https://github.com/drush-ops/drush/blob/8.x/examples/policy.drush.inc
 * Encourage folks to use `composer` instead of Drush pm commands
 */
function drush_policy_pm_updatecode_validate() {
  return _deny_message();
}

function drush_policy_pm_update_validate() {
  return _deny_message();
}

function drush_policy_pm_download_validate() {
  $module = array_shift(pm_parse_arguments(func_get_args()));
  return _deny_message($module);
}

function _deny_message($module = 'references') {
  if (!drush_get_option('pm-force')) {
    $msg = "Drush dl forbidden! Use Composer: drush up = `composer update` & drush dl $module = `composer require drupal/$module`.";

    return drush_set_error('POLICY_PM_DENY', dt($msg));
  }
}

/**
 * Implements hook_drush_help_alter().
 */
function policy_drush_help_alter(&$command) {
  if (in_array($command['command'], ['pm-updatecode', 'pm-update', 'pm-download'])) {
    $command['options']['pm-force'] = 'Override site policy and allow Drush codebase management (pm-* commands)';
  }
}


