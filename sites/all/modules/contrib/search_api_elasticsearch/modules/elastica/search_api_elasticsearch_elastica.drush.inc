<?php
/**
 * @file
 * Drush integration for Search API elasticsearch.
 */

/**
 * The current version of Elastica.
 *
 * @var string
 */
const SEARCH_API_ELASTICSEARCH_ELASTICA_VERSION = '1.3.0.0';

/**
 * The URL of the Elastica Github Tarball.
 *
 * @var string
 */
define('SEARCH_API_ELASTICSEARCH_ELASTICA_TARBALL', 'https://github.com/ruflin/Elastica/archive/v' . SEARCH_API_ELASTICSEARCH_ELASTICA_VERSION . '.tar.gz');

/**
 * Implements hook_drush_command().
 */
function search_api_elasticsearch_elastica_drush_command() {
  $items['download-elastica'] = array(
    'description' => dt("Downloads the Elastica PHP Library."),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'callback' => 'drush_download_elastica',
  );
  return $items;
}

/**
 * Command to download the Elastica PHP Library.
 */
function drush_download_elastica() {
  module_load_include('module', 'search_api_elasticsearch_elastica');
  spl_autoload_register('_search_api_elasticsearch_elastica_autoload');

  // Check if the library is already installed.
  if (class_exists('Elastica\Client')) {
    drush_log(dt('Elastica library already present. No download required.'), 'ok');
    return;
  }

  if (module_exists('composer_manager')) {
    drush_shell_exec('drush composer-manager install');
  }
  else {
    if (!drush_shell_exec('type tar')) {
      return drush_set_error(dt('Missing dependency: tar. Install it before using this command.'));
    }

    $args = func_get_args();
    if (isset($args[0])) {
      $path = $args[0];
    }
    else {
      $path = drush_get_context('DRUSH_DRUPAL_ROOT');
      // If the library exists, get its directory.
      if (module_exists('libraries') && $library_path = dirname(libraries_get_path('Elastica'))) {
        $path .= '/' . $library_path;
      }
      else {
        $path .= '/sites/all/libraries';
      }
    }

    // Create the path if it does not exist.
    if (!is_dir($path)) {
      drush_op('mkdir', $path);
      drush_log(dt('Directory @path was created', array('@path' => $path)), 'notice');
    }

    // Keep the old directory for later.
    $olddir = getcwd();
    // Set the directory to the download location.
    chdir($path);

    // Download the tar.
    _search_api_elasticsearch_elastica_fetch_tar(SEARCH_API_ELASTICSEARCH_ELASTICA_TARBALL);

    // Set working directory back to the previous working directory.
    chdir($olddir);

    if (is_dir($path . '/Elastica')) {
      drush_log(dt('Elastica has been downloaded to @path', array('@path' => $path)), 'success');
    }
    else {
      drush_log(dt('Drush was unable to download Elastica to @path', array('@path' => $path)), 'error');
    }
  }
}

/**
 * Downloads and extracts a tarball.
 */
function _search_api_elasticsearch_elastica_fetch_tar($url) {
  $filename = basename($url);

  // Remove any existing files.
  if (is_file($filename)) {
    drush_op('unlink', $filename);
  }

  if (!drush_shell_exec('wget ' . $url)) {
    drush_shell_exec('curl -O ' . $url);
  }

  if (is_file($filename)) {
    print $filename;
    // Extract the archive.
    drush_shell_exec('tar xf ' . $filename);
    // Remove the file.
    drush_op('unlink', $filename);
    // Move the directory.
    drush_shell_exec('mv Elastica-' . SEARCH_API_ELASTICSEARCH_ELASTICA_VERSION . ' Elastica');
  }
}

/**
 * Implements drush_MODULE_pre_COMMAND().
 *
 * Attempt to download the Elastica library if not already there.
 */
function drush_search_api_elasticsearch_elastica_pre_pm_enable() {
  $modules = func_get_args();
  if (in_array('search_api_elasticsearch_elastica', $modules)) {
    drush_download_elastica();
  }
}
