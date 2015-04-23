<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\ContentTypeConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class ContentTypeConfiguration extends Configuration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::__construct().
   */
  function __construct($identifier, $component = '') {
    parent::__construct($identifier);
    $keys = array(
      'type',
      'name',
      'description',
      'has_title',
      'title_label',
      'base',
      'help',
    );
    $this->setKeysToExport($keys);
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'content_type';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('content_type');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Content types') : t('Content type');
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
    return 'node';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    return node_type_get_names();
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findRequiredModules().
   */
  public function findRequiredModules() {
    if ($this->data->base == 'node_content' || $this->data->base == 'configuration') {
      $this->addToModules('node');
    }
    else {
      $this->addToModules($this->data->base);
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->getComponent() == 'permission') {

      foreach (node_permissions_get_configured_types() as $type) {
        foreach (array_keys(node_list_permissions($type)) as $permission) {
          $data = $config->getData();
          if ($permission == $data['permission']) {
            $content_type = ConfigurationManagement::createConfigurationInstance('content_type.' . $type);
            $config->addToDependencies($content_type);
            break;
          }
        }
      }
    }
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::prepareBuild().
   */
  protected function prepareBuild() {
    $data = (object)node_type_get_type($this->identifier);

    $this->data = new \StdClass();
    foreach ($this->getKeysToExport() as $key) {
      $this->data->$key = $data->$key;
    }

    // Force module name to be 'configuration' if set to 'node. If we leave as
    // 'node' the content type will be assumed to be database-stored by
    // the node module.
    $this->data->base = ($this->data->base === 'node') ? 'configuration' : $this->data->base;
    return $this;
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    $info = (object)$this->getData();
    $info->base = 'node_content';
    $info->module = 'node';
    $info->custom = 1;
    $info->modified = 1;
    $info->locked = 0;
    node_type_save($info);

    $settings->addInfo('imported', $this->getUniqueId());
  }
}
