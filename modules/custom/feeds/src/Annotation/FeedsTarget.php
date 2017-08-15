<?php

namespace Drupal\feeds\Annotation;

/**
 * Defines a Plugin annotation object for Feeds target plugins.
 *
 * Plugin Namespace: Feeds\Target
 *
 * For a working example, see \Drupal\feeds\Feeds\Target\Text.
 *
 * @see \Drupal\feeds\Plugin\Type\FeedsPluginManager
 * @see \Drupal\feeds\Plugin\Type\Target\TargetInterface
 * @see \Drupal\feeds\Plugin\Type\PluginBase
 * @see plugin_api
 *
 * @Annotation
 */
class FeedsTarget extends FeedsBase {

  /**
   * The field types a target plugin applies to.
   *
   * @var array
   */
  public $field_types;

}
