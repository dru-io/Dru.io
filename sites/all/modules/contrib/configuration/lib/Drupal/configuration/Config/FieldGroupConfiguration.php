<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\FieldGroupHandlerConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\CtoolsConfiguration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class FieldGroupConfiguration extends CtoolsConfiguration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::isActive().
   */
  public static function isActive() {
    return module_exists('field_group');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Field Groups') : t('Field Group');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'field_group';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('field_group');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    // @todo Implement dependency logic related to Content Type.
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    ctools_include('export');
    $object = ctools_export_crud_load($this->getComponent(), $this->getIdentifier());
    if ($object) {
      ctools_export_crud_delete($this->getComponent(), $object);
    }
    $data = $this->getData();
    $data->export_type = NULL;
    $group = field_group_unpack($data);
    ctools_export_crud_save($this->getComponent(), $group);
    $settings->addInfo('imported', $this->getUniqueId());
  }

}
