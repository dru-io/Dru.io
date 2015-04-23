<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\MenuConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class MenuConfiguration extends Configuration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::isActive().
   */
  public static function isActive() {
    return module_exists('menu');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Menus') : t('Menu');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'menu';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('menu');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    $menus = db_query("SELECT menu_name, title FROM {menu_custom}")->fetchAll();
    $return = array();
    foreach ($menus as $menu) {
      $return[str_replace('-', '_', $menu->menu_name)] = $menu->title;
    }
    return $return;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->getComponent() == 'menu_link') {
      $config_data = $config->getData();
      if (!empty($config_data) && empty($config_data['plid'])) {
        $menu = new MenuConfiguration(str_replace('-', '_', $config_data['menu_name']));
        $menu->build();
        $config->addToDependencies($menu);
      }
    }
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::prepareBuild().
   */
  protected function prepareBuild() {
    $this->data = menu_load(str_replace('_', '-', $this->getIdentifier()));
    return $this;
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    menu_save($this->getData());
    $settings->addInfo('imported', $this->getUniqueId());
  }


}
