<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\TextFormatConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class TextFormatConfiguration extends Configuration {


  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Text formats') : t('Text format');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'text_format';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('text_format');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    $identifiers = array();
    foreach (filter_formats() as $format) {
      $identifiers[$format->format] = $format->name;
    }
    return $identifiers;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findRequiredModules().
   */
  public function findRequiredModules() {
    $filter_info = filter_get_filters();
    foreach (array_keys($this->data->filters) as $filter) {
      if (!empty($filter_info[$filter]) && $filter_info[$filter]['module']) {
        $this->addToModules($filter_info[$filter]['module']);
      }
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->getComponent() == 'permission') {

    // Generate permissions for each text format. Warn the administrator that any
    // of them are potentially unsafe.
    foreach (filter_formats() as $format) {
      $permission = filter_permission_name($format);
      if (!empty($permission)) {
          $data = $config->getData();
          if ($permission == $data['permission']) {
            $text_format = ConfigurationManagement::createConfigurationInstance('text_format.' . $format->format);
            $config->addToDependencies($text_format);
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
    $name = $this->getIdentifier();

    // Use machine name for retrieving the format if available.
    $query = db_select('filter_format');
    $query->fields('filter_format');
    $query->condition('format', $name);

    // Retrieve filters for the format and attach.
    if ($format = $query->execute()->fetchObject()) {
      $format->filters = array();
      foreach (filter_list_format($format->format) as $filter) {
        if (!empty($filter->status)) {
          $format->filters[$filter->name]['weight'] = $filter->weight;
          $format->filters[$filter->name]['status'] = $filter->status;
          $format->filters[$filter->name]['settings'] = $filter->settings;
        }
      }
      $this->data = $format;
      return $this;
    }
    else {
      $this->data = FALSE;
    }
    return $this;
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    filter_format_save($this->getData());
    $settings->addInfo('imported', $this->getUniqueId());
  }
}
