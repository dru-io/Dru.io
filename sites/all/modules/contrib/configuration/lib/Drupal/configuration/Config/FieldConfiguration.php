<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\FieldConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class FieldConfiguration extends Configuration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Fields') : t('Field');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'field';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('field');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    $identifiers = array();
    foreach (field_info_fields() as $field) {
      foreach ($field['bundles'] as $entity_type => $bundles) {
        foreach ($bundles as $bundle_name) {
          $identifiers[$entity_type . '.' . $field['field_name'] . '.' . $bundle_name] = t('@field used in (@entity.@bundle)', array('@field' => $field['field_name'], '@entity' => $entity_type, '@bundle' => $bundle_name));
        }
      }
    }
    return $identifiers;
  }

  /**
   * Load a field's configuration and instance configuration by an
   * entity_type.bundle.field_name identifier.
   */
  protected function field_load($identifier) {
    list($entity_type, $field_name, $bundle) = explode('.', $identifier);
    $field_info = field_info_field($field_name);
    $instance_info = field_info_instance($entity_type, $field_name, $bundle);
    if ($field_info && $instance_info) {
      unset($field_info['id']);
      unset($field_info['bundles']);
      unset($instance_info['id']);
      unset($instance_info['field_id']);
      return array(
        'field_config' => $field_info,
        'field_instance' => $instance_info,
      );
    }
    return FALSE;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->configForEntity()) {
      $entity_type = $config->getEntityType();
      if (empty($entity_type)) {
        return;
      }
      $fields = field_info_instances($entity_type, $config->getIdentifier());
      foreach ($fields as $name => $field) {
        $identifier = $entity_type . "." . $field['field_name'] . "." . $field['bundle'];

        // Avoid include multiple times the same dependency.
        if (empty($stack['field.' . $identifier])) {
          $field = new FieldConfiguration($identifier);
          $field->build();
          $field->addToDependencies($config);
          $config->addToOptionalConfigurations($field);
          $stack['field.' . $identifier] = TRUE;
        }
      }
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findDependencies().
   */
  public function findDependencies() {
    list($entity_type, $field_name, $bundle_name) = explode('.', $this->getIdentifier());

    $supported_handler = FALSE;
    if ($entity_type == 'node') {
      $parent_config = ConfigurationManagement::createConfigurationInstance('content_type.' . $bundle_name);
      $this->addToDependencies($parent_config);
    }
    elseif ($entity_type == 'vocabulary') {
      $parent_config = ConfigurationManagement::createConfigurationInstance('vocabulary.' . $bundle_name);
      $this->addToDependencies($parent_config);
    }
    parent::findDependencies();
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findRequiredModules().
   */
  public function findRequiredModules() {
    $this->addToModules($this->data['field_config']['storage']['module']);
    $this->addToModules($this->data['field_config']['module']);
    $this->addToModules($this->data['field_instance']['widget']['module']);
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::prepareBuild().
   */
  protected function prepareBuild() {
    $this->data = $this->field_load($this->identifier);
    if (empty($this->data)) {
      $this->data = NULL;
    }
    return $this;
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    field_info_cache_clear();

    // Load all the existing fields and instance up-front so that we don't
    // have to rebuild the cache all the time.
    $existing_fields = field_info_fields();
    $existing_instances = field_info_instances();

    $field = $this->getData();
    // Create or update field.
    $field_config = $field['field_config'];
    if (isset($existing_fields[$field_config['field_name']])) {
      $existing_field = $existing_fields[$field_config['field_name']];
      if ($field_config + $existing_field != $existing_field) {
        field_update_field($field_config);
      }
    }
    else {
      field_create_field($field_config);
      $existing_fields[$field_config['field_name']] = $field_config;
    }

    // Create or update field instance.
    $field_instance = $field['field_instance'];
    if (isset($existing_instances[$field_instance['entity_type']][$field_instance['bundle']][$field_instance['field_name']])) {
      $existing_instance = $existing_instances[$field_instance['entity_type']][$field_instance['bundle']][$field_instance['field_name']];
      if ($field_instance + $existing_instance != $existing_instance) {
        field_update_instance($field_instance);
      }
    }
    else {
      field_create_instance($field_instance);
      $existing_instances[$field_instance['entity_type']][$field_instance['bundle']][$field_instance['field_name']] = $field_instance;
    }

    variable_set('menu_rebuild_needed', TRUE);
    $settings->addInfo('imported', $this->getUniqueId());
  }
}
