<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\EntityApiConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class EntityApiConfiguration extends Configuration {

  /**
   * The component of the current configuration.
   *
   * Usually this component is the entity type.
   *
   * @var string
   */
  protected $component;

  /**
   * Overrides Drupal\configuration\Config\Configuration::__construct().
   */
  public function __construct($identifier, $component = '') {
    // Because Entity API can handle multiple types of configurations we need to
    // know what is the current handled configuration. Usually this component is
    // the entity type.
    $this->component = $component;
    parent::__construct($identifier, $component);
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getStorageSystem().
   */
  static protected function getStorageSystem($component) {
    return '\Drupal\configuration\Storage\StorageEntityApi';
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
    if (module_exists('entity')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    $entity_info = entity_crud_get_info();
    if ($plural && !empty($entity_info[$component]['plural_label'])) {
      return $entity_info[$component]['plural_label'];
    }
    else {
      return $entity_info[$component]['label'];
    }
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
    $supported = array();
    foreach (entity_crud_get_info() as $type => $info) {
      if (!empty($info['exportable'])) {
        $supported[] = $type;
      }
    }
    return $supported;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    $options = array();
    foreach (entity_load_multiple_by_name($component, FALSE) as $name => $entity) {
      $options[$name] = entity_label($component, $entity);
    }
    return $options;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::configForEntity().
   */
  public function configForEntity() {
    return TRUE;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getEntityType().
   */
  public function getEntityType() {
    $entity = entity_load_single($this->getComponent(), $this->getIdentifier());
    $info = $entity->entityInfo();
    if (!empty($info['bundle of'])) {
      return $info['bundle of'];
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findRequiredModules().
   */
  public function findRequiredModules() {
    $this->addToModules('entity');
    $entity_info = entity_get_info($this->getComponent());

    $entity = $this->getData();
    if (!empty($entity->dependencies)) {
      foreach ($entity->dependencies as $dependency) {
        $this->addToModules($dependency);
      }
    }

    if ($entity_info) {
      $this->addToModules($entity_info['module']);
    }
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::prepareBuild().
   */
  public function prepareBuild() {
    $entity_type = $this->getComponent();
    $this->data = entity_load_single($entity_type, $this->getIdentifier());
    return $this;
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    $entity = $this->getData();
    $entity_type = $this->getComponent();
    if ($original = entity_load_single($entity_type, $this->getIdentifier())) {
      $entity->id = $original->id;
      unset($entity->is_new);
    }

    $entity->save();
    $settings->addInfo('imported', $this->getUniqueId());
  }
}
