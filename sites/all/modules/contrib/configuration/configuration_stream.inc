<?php
/**
 * @file
 * Provides a config:// streamwrapper.
 *
 * Stream Wrapper for accessing the configurations in a Drupal site.
 */

/**
 * Stream wrapper class to handle config:// streams.
 */
class ConfigurationStreamWrapper extends DrupalLocalStreamWrapper {
  /**
   * Implements getDirectoryPath().
   *
   * In this case there is no directory string, so return an empty string.
   */
  public function getDirectoryPath() {
    return variable_get('configuration_config_path', conf_path() . '/files/config');
  }

  /**
   * Overrides getExternalUrl().
   *
   * We will not need to access this from an Url.
   */
  function getExternalUrl() {
    return FALSE;
  }
}
