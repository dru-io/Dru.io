<?php

/**
 * @file
 * Bootstrap Drupal for PHPUnit.
 */

// Loop through parent directories until we locate DRUPAL_ROOT.
while (!file_exists('index.php')) {
  chdir('..');
}

define('DRUPAL_ROOT', getcwd());
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
