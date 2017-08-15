<?php

namespace Drupal\feeds\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Base annotation class for Feeds plugins.
 */
abstract class FeedsBase extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The title of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * An optional form class that is separate from the plugin.
   *
   * It must implement \Drupal\feeds\Plugin\Type\ExternalPluginFormInterface.
   *
   * @var string
   */
  public $configuration_form;

  /**
   * Constructor arguments.
   *
   * @var array
   */
  public $arguments;

}
