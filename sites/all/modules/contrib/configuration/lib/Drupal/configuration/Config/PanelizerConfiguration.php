<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\PanelizerConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\CtoolsConfiguration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class PanelizerConfiguration extends CtoolsConfiguration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::isActive().
   */
  public static function isActive() {
    return module_exists('panelizer');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return t('Panelizer');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'panelizer_defaults';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('panelizer_defaults');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->getComponent() == 'permission') {
      $panelizers = static::getAllIdentifiers('panelizer_defaults');

      $permission = $config->getData();
      if (strpos($permission['permission'], 'administer panelizer ') === 0) {
        list(,, $entity_type, $bundle) = explode(' ', $permission['permission']);
        $id = $entity_type . ':' . $bundle;
        foreach ($panelizers as $panelizer_id) {
          if (strpos($panelizer_id, $id) === 0) {
            $panelizer = ConfigurationManagement::createConfigurationInstance('panelizer_defaults.' . $panelizer_id);
            $panelizer->build();
            $config->addToDependencies($panelizer);
          }
        }
      }
    }
  }
}
