<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\ImageStyleConfiguration.
 */

namespace Drupal\configuration\Config;

use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;

class ImageStyleConfiguration extends Configuration {

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponentHumanName().
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return $plural ? t('Image styles') : t('Image style');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getComponent().
   */
  public function getComponent() {
    return 'image_style';
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::supportedComponents().
   */
  static public function supportedComponents() {
    return array('image_style');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::isActive().
   */
  public static function isActive() {
    return module_exists('image');
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::getAllIdentifiers().
   */
  public static function getAllIdentifiers($component) {
    $identifiers = array();
    foreach (image_styles() as $key => $image_style) {
      $identifiers[$key] = $image_style['name'];
    }
    return $identifiers;
  }

  /**
   * Remove unnecessary keys for export.
   */
  protected function style_sanitize(&$style, $child = FALSE) {
    $omit = $child ? array('isid', 'ieid') : array('isid', 'ieid', 'module');
    if (is_array($style)) {
      foreach ($style as $k => $v) {
        if (in_array($k, $omit, TRUE)) {
          unset($style[$k]);
        }
        elseif (is_array($v)) {
          $this->style_sanitize($style[$k], TRUE);
        }
      }
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::alterDependencies().
   */
  public static function alterDependencies(Configuration $config) {
    if ($config->getComponent() == 'field') {
      // Check if the field is using a image style
      $field = $config->data['field_instance'];
      if (!empty($field['display'])) {
        foreach ($field['display'] as $display) {
          if (!empty($display['settings']) && !empty($display['settings']['image_style'])) {
            $identifier = $display['settings']['image_style'];

            $image_style = new ImageStyleConfiguration($identifier);
            $image_style->build();
            $config->addToDependencies($image_style);
          }
        }
      }
    }
  }

  /**
   * Overrides Drupal\configuration\Config\Configuration::findRequiredModules().
   */
  public function findRequiredModules() {
    foreach ($this->data['effects'] as $effect) {
      $this->addToModules($effect['module']);
    }
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::prepareBuild().
   */
  protected function prepareBuild() {
    $style = image_style_load($this->getIdentifier());
    $this->style_sanitize($style);
    $this->data = $style;

    // Reset the order of effects, this will help to generate always the same
    // hash for image styles that have been reverted.
    $this->data['effects'] = array();
    if (!empty($style['effects'])) {
      foreach ($style['effects'] as $effect) {
        $this->data['effects'][] = $effect;
      }
    }
    return $this;
  }

  /**
   * Implements Drupal\configuration\Config\Configuration::saveToActiveStore().
   */
  public function saveToActiveStore(ConfigIteratorSettings &$settings) {
    $style = $this->getData();

    // Does an image style with the same name already exist?
    if ($existing_style = image_style_load($this->getIdentifier())) {
      $isExistingEditable = (bool)($existing_style['storage'] & IMAGE_STORAGE_EDITABLE);
      $isNewEditable = (bool)($style['storage'] & IMAGE_STORAGE_EDITABLE);

      // New style is using defaults -> revert existing.
      if (!$isNewEditable && $isExistingEditable) {
        image_default_style_revert($this->getIdentifier());
      }

      // New style is editable -> update existing style.
      elseif ($isExistingEditable && $isNewEditable) {
        $style['isid'] = $existing_style['isid'];
        $style = image_style_save($style);
        if (!empty($existing_style['effects'])) {
          foreach ($existing_style['effects'] as $effect) {
            image_effect_delete($effect);
          }
        }
        if (!empty($style['effects'])) {
          foreach ($style['effects'] as $effect) {
            $effect['isid'] = $style['isid'];
            image_effect_save($effect);
          }
        }
      }

      // New style is editable, existing style is using defaults -> update without deleting effects.
      elseif($isNewEditable && !$isExistingEditable) {
        if (!empty($existing_style['isid'])) {
          $style['isid'] = $existing_style['isid'];
        }
        $style = image_style_save($style);
        if (!empty($style['effects'])) {
          foreach ($style['effects'] as $effect) {
            $effect['isid'] = $style['isid'];
            image_effect_save($effect);
          }
        }
      }

      // Neither style is editable, both default -> do nothing at all.
      else {

      }
    }

    // New style does not exist yet on this system -> save it regardless of its storage.
    else {
      $style = image_style_save($style);
      if (!empty($style['effects'])) {
        foreach ($style['effects'] as $effect) {
          $effect['isid'] = $style['isid'];
          image_effect_save($effect);
        }
      }
      image_style_flush($style);
    }

    $settings->addInfo('imported', $this->getUniqueId());
  }
}
