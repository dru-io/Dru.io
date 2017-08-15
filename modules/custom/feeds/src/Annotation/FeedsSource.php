<?php

namespace Drupal\feeds\Annotation;

/**
 * Defines a Plugin annotation object for Feeds source plugins.
 *
 * Plugin Namespace: Feeds\Source
 *
 * For a working example, see \Drupal\feeds\Feeds\Source\BasicFieldSource.
 *
 * @see \Drupal\feeds\Plugin\Type\FeedsPluginManager
 * @see \Drupal\feeds\Plugin\Type\Source\SourceInterface
 * @see \Drupal\feeds\Plugin\Type\PluginBase
 * @see plugin_api
 *
 * @Annotation
 */
class FeedsSource extends FeedsBase {

  /**
   * The field types a source plugin applies to.
   *
   * @var array
   */
  public $field_types;

}
