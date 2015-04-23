<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\VariableConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class VariableConfiguration extends Configuration {

  protected $variable_name = '';

  /**
   * Overrides Drupal\configuration\Config\Configuration::__construct().
   */
  public function __construct($identifier, $component = '') {
    $this->variable_name = $identifier;
    parent::__construct(str_replace(' ', '_', $identifier));
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Variables') : t('Variable');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'variable';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('variable');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    $variables = db_query("SELECT name FROM {variable}")->fetchAll();
    $return = array();
    foreach ($variables as $variable) {
      $return[$variable->name] = $variable->name;
    }
    return $return;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->getComponent() == 'content_type') {
      $variables = array(
        'field_bundle_settings_node_',
        'language_content_type',
        'node_options',
        'node_preview',
        'node_submitted',
      );

      if (module_exists('comment')) {
        $variables += array(
          'comment',
          'comment_anonymous',
          'comment_controls',
          'comment_default_mode',
          'comment_default_order',
          'comment_default_per_page',
          'comment_form_location',
          'comment_preview',
          'comment_subject_field',
        );
      }

      if (module_exists('menu')) {
        $variables += array(
          'menu_options',
          'menu_parent',
        );
      }

      $entity_type = $config->getEntityType();
      $fields = field_info_instances($entity_type, $config->getIdentifier());
      foreach ($variables as $variable) {
        $identifier = $variable . '_' . $config->getIdentifier();

        $in_db = db_query("SELECT 1 FROM {variable} WHERE name = :name", array(':name' => $identifier))->fetchField();
        // Some variables are not in the database and their values are
        // provided by the second paramenter of variable_get.
        // Only inform about configurations that are indeed in the database.
        if ($in_db) {
          $var_config = new VariableConfiguration($identifier);
          $var_config->build();
          $config->addToDependencies($var_config);
        }
      }
    }
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::prepareBuild().
   */
  protected function prepareBuild() {
    $this->data = array(
      'name' => $this->variable_name,
      'content' => variable_get($this->getIdentifier(), NULL),
    );
    return $this;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    $variable = $this->getData();
    variable_set($variable['name'], $variable['content']);
    $settings->addInfo('imported', $this->getUniqueId());
  }
}
