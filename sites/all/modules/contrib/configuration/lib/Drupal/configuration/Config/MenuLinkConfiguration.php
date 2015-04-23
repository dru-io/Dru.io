<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\FieldConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class MenuLinkConfiguration extends Configuration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::__construct().
   */
  public function __construct($identifier) {
    parent::__construct($identifier);
    $keys = array(
      'link_path',
      'link_title',
      'menu_name',
      'weight',
      'expanded',
      'options',
      'router_path',
      'parent_identifier',
    );
    $this->setKeysToExport($keys);
  }

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
    return $plural ? t('Menu links') : t('Menu link');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'menu_link';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('menu_link');
  }

  /**
   * Helper function to retrive a menu link based on its identifier.
   *
   * @param string $identifier
   *   The identifier of the configuration.
   * @param boolean $reset
   *   If TRUE the cache of this function is flushed.
   *
   * @return
   *   A menu link object.
   */
  static public function getMenuLinkByIdenfifier($identifier, $reset = FALSE) {
    static $menu_links;

    if (!isset($menu_links) || $reset) {
      $res = db_select('menu_links', 'ml')
                        ->fields('ml', array('menu_name', 'link_path', 'mlid'))
                        ->execute()
                        ->fetchAll();

      $menu_links = array();
      foreach ($res as $menu_link) {
        $id = sha1(str_replace('-', '_', $menu_link->menu_name) . ':' . $menu_link->link_path);
        $menu_links[$id] = $menu_link->mlid;
      }
    }
    if (!empty($menu_links[$identifier])) {
      return $menu_links[$identifier];
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    global $menu_admin;
    // Need to set this to TRUE in order to get menu links that the
    // current user may not have access to (i.e. user/login)
    $menu_admin = TRUE;

    // This is intentionally to get always the same number of menus for each
    // user that can manage configurations.
    global $user;
    $current_user = $user;
    // Run the next line as administrator.
    $user = user_load(1);
    $menu_links = menu_parent_options(menu_get_menus(), array('mlid' => 0));
    // Back to the previous user.
    $user = $current_user;

    $options = array();
    foreach ($menu_links as $key => $name) {
      list($menu_name, $mlid) = explode(':', $key, 2);
      if ($mlid != 0) {
        $link = menu_link_load($mlid);
        $identifier = sha1(str_replace('-', '_', $link['menu_name']) . ':' . $link['link_path']);
        $options[$identifier] = "{$menu_name}: {$name}";
      }
    }
    $menu_admin = FALSE;
    return $options;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->getComponent() == 'menu_link') {
      $mlid = static::getMenuLinkByIdenfifier($config->getIdentifier());
      $menulink = menu_link_load($mlid);
      if (!empty($menulink['plid'])) {
        $parent_menulink = menu_link_load($menulink['plid']);
        if ($parent_menulink) {
          $identifier = sha1(str_replace('-', '_', $parent_menulink['menu_name']) . ':' . $parent_menulink['link_path']);
          $menulink_config = new MenuLinkConfiguration($identifier);
          $menulink_config->build();
          $config->addToDependencies($menulink_config);
        }
      }
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::prepareBuild().
   */
  protected function prepareBuild() {
    $mlid = static::getMenuLinkByIdenfifier($this->getIdentifier(), TRUE);
    $this->data = menu_link_load($mlid);
    $this->data['parent_identifier'] = NULL;

    if (!empty($this->data['plid'])) {
      $parent = db_select('menu_links', 'ml')
                          ->fields('ml', array('menu_name', 'link_path', 'mlid'))
                          ->condition('mlid', $this->data['plid'])
                          ->execute()
                          ->fetchObject();
      if (!empty($parent)) {
        $this->data['parent_identifier'] = sha1(str_replace('-', '_', $parent->menu_name) . ':' . $parent->link_path);
      }
    }
    return $this;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    $data = $this->getData();

    // Determine if the menu already exists.
    $data['mlid'] = static::getMenuLinkByIdenfifier($this->getIdentifier());

    if (!empty($data['parent_identifier'])) {
      $data['plid'] = static::getMenuLinkByIdenfifier($this->data['parent_identifier'], TRUE);
    }
    menu_link_save($data);
    $settings->addInfo('imported', $this->getUniqueId());
  }

}
