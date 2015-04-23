<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\WysiwygConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Config\ConfigurationManagement;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class WysiwygConfiguration extends Configuration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Wyswyg Profiles') : t('Wyswyg Profile');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::isActive().
   */
  public static function isActive() {
    return module_exists('wysiwyg');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'wysiwyg';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('wysiwyg');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    $profiles = array();

    $formats = filter_formats();

    foreach (array_keys(wysiwyg_profile_load_all()) as $format) {
      // Text format may vanish without deleting the wysiwyg profile.
      if (isset($formats[$format])) {
        $profiles[$format] = $format;
      }
    }
    return $profiles;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->getComponent() == 'text_format') {
      $formats = filter_formats();
      foreach (array_keys(wysiwyg_profile_load_all()) as $format) {
        // Text format may vanish without deleting the wysiwyg profile.
        if (isset($formats[$format]) && $format == $config->getIdentifier()) {
          $identifier = $format;
          $wysiwig_profile = new WysiwygConfiguration($identifier);
          $wysiwig_profile->build();
          $config->addToOptionalConfigurations($wysiwig_profile);
          $wysiwig_profile->addToDependencies($config);
        }
      }
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findDependencies().
   */
  public function findDependencies() {
    $format = $this->getIdentifier();

    $formats = filter_formats();
    if (isset($formats[$format])) {
      $filter_format = ConfigurationManagement::createConfigurationInstance('text_format.' . $format);
      $this->addToDependencies($filter_format);
    }

    parent::findDependencies();
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findRequiredModules().
   */
  public function findRequiredModules() {
    $this->addToModules('wysiwyg');
    // @todo figure out if there is a way to add modules that provides plugins
    // for this wysiwyg
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::prepareBuild().
   */
  protected function prepareBuild() {
    $this->data = wysiwyg_get_profile($this->getIdentifier());
    return $this;
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    $profile = $this->getData();

    // For profiles that doens't have editors assigned, provide a default
    // object to avoid sql exceptions.
    if (empty($profile)) {
      $profile = new \StdClass();
      $profile->editor = '';
      $profile->format = $this->getIdentifier();
      $profile->settings = array();
    }

    db_merge('wysiwyg')
      ->key(array('format' => $profile->format))
      ->fields(array(
        'format' => $profile->format,
        'editor' => $profile->editor,
        'settings' => serialize($profile->settings),
      ))
      ->execute();
    wysiwyg_profile_cache_clear();

    $settings->addInfo('imported', $this->getUniqueId());
  }
}
