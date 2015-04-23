<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\CtoolsConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class CtoolsConfiguration extends Configuration {

  /**
   * The component of the current configuration.
   *
   * Usually this component is the table where the configuration object lives.
   *
   * @var string
   */
  protected $component;

  /**
   * Overrides Drupal\configuration\Config\Configuration::__construct().
   */
  public function __construct($identifier, $component = '') {
    // Because CTools can handle multiple types of configurations we need to
    // know what is the current handled configuration. Usually this component is
    // the main table where the ctools object lives.
    $this->component = $component;
    parent::__construct($identifier, $component);
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getStorageInstance().
   */
  static protected function getStorageInstance($component) {
    $storage = static::getStorageSystem($component);
    return new $storage($component);
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::isActive().
   */
  public static function isActive() {
    return module_exists('ctools');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    ctools_include('export');
    foreach (ctools_export_get_schemas_by_module() as $module => $schemas) {
      if (!empty($schemas[$component])) {
        if (!empty($schemas[$component]['export']['identifier'])) {
          return $schemas[$component]['export']['identifier'];
        }
        return $component;
      }
    }
    return '';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return $this->component;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    if (!static::isActive()) {
      return array();
    }

    $supported = array();
    ctools_include('export');
    foreach (ctools_export_get_schemas_by_module() as $module => $schemas) {
      foreach ($schemas as $table => $schema) {
        if (isset($schema['export']) && $schema['export']['bulk export']) {
          $supported[] = $table;
        }
      }
    }
    return $supported;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    ctools_include('export');
    $objects = ctools_export_load_object($component, 'all');
    return drupal_map_assoc(array_keys($objects));
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getStorageSystem().
   */
  static protected function getStorageSystem($component) {
    return '\Drupal\configuration\Storage\StorageCtools';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findRequiredModules().
   */
  public function findRequiredModules() {
    $this->addToModules('ctools');
    foreach (ctools_export_get_schemas_by_module() as $module => $schemas) {
      foreach ($schemas as $table => $schema) {
        if ($table == $this->getComponent()) {
          $this->addToModules($module);
        }
      }
    }
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::prepareBuild().
   */
  public function prepareBuild() {
    ctools_include('export');
    ctools_export_load_object_reset();
    $this->data = ctools_export_crud_load($this->getComponent(), $this->getIdentifier());
    return $this;
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
    ctools_export_crud_save($this->getComponent(), $data);
    $settings->addInfo('imported', $this->getUniqueId());
  }
}
