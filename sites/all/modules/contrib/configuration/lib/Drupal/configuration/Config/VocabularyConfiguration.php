<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\VocabularyConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class VocabularyConfiguration extends Configuration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::configForEntity().
   */
  public function configForEntity() {
    return TRUE;
  }
  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Vocabularies') : t('Vocabulary');
  }
  /**
   * Overrides Drupal\configuration\Config\Configuration::isActive().
   */
  public static function isActive() {
    return module_exists('taxonomy');
  }
  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'vocabulary';
  }
  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('vocabulary');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getEntityType().
   */
  public function getEntityType() {
    return 'vocabulary';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    $return = array();
    $vocabularies = taxonomy_get_vocabularies();
    foreach ($vocabularies as $vocabulary) {
      $return[$vocabulary->machine_name] = $vocabulary->name;
    }
    return $return;
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->getComponent() == 'field') {
      // Check if the field is using a image style
      $field = $config->data['field_config'];
      if ($field['type'] == 'taxonomy_term_reference' && $field['settings']['allowed_values']) {
        foreach ($field['settings']['allowed_values'] as $vocabulary) {

          $vocabulary_conf = new VocabularyConfiguration($vocabulary['vocabulary']);
          $vocabulary_conf->build();
          $config->addToDependencies($vocabulary_conf);

        }
      }
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findRequiredModules().
   */
  public function findRequiredModules() {
    $this->addToModules($this->data->module);
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::prepareBuild().
   */
  protected function prepareBuild() {
    $vocabularies = taxonomy_get_vocabularies();
    foreach ($vocabularies as $vocabulary) {
      if ($vocabulary->machine_name == $this->getIdentifier()) {
        $this->data = $vocabulary;
        break;
      }
    }
    return $this;
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    $vocabulary = (object) $this->getData();
    if (!empty($vocabulary->vid)) {
      unset($vocabulary->vid);
    }
    $existing = taxonomy_get_vocabularies();
    foreach ($existing as $existing_vocab) {
      if ($existing_vocab->machine_name === $vocabulary->machine_name) {
        $vocabulary->vid = $existing_vocab->vid;
        break;
      }
    }
    taxonomy_vocabulary_save($vocabulary);
    $settings->addInfo('imported', $this->getUniqueId());
  }
}
