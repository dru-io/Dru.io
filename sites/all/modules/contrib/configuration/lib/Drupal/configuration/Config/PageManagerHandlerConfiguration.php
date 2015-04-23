<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\PageManagerHandlerConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\CtoolsConfiguration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class PageManagerHandlerConfiguration extends CtoolsConfiguration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::isActive().
   */
  public static function isActive() {
    return module_exists('page_manager');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Page Manager Handlers') : t('Page Manage Handler');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'page_manager_handlers';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('page_manager_handlers');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    // Dependencies for Page Manager Pages. Each page has a handler.
    if ($config->getComponent() == 'page_manager_pages' && !$config->broken) {
      $config_data = $config->getData();
      $id = 'page_manager_handlers.page_' . $config_data->name . '_panel_context';

      $page_handler = ConfigurationManagement::createConfigurationInstance($id);
      $page_handler->build();
      $config->addToDependencies($page_handler);

    }
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
    panels_save_display($data->conf['display']);
    $data->conf['did'] = $data->conf['display']->did;
    unset($data->conf['display']);
    ctools_export_crud_save($this->getComponent(), $data);
    $settings->addInfo('imported', $this->getUniqueId());
  }

}
