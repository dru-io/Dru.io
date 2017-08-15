<?php

namespace Drupal\feeds\Plugin\Type;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface for Feeds plugins that have an external form.
 */
interface ExternalPluginFormInterface extends PluginFormInterface {

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param \Drupal\feeds\Plugin\Type\FeedsPluginInterface $plugin
   *   The plugin.
   *
   * @return static
   *   Returns an instance of this plugin form.
   */
  public static function create(ContainerInterface $container, FeedsPluginInterface $plugin);

}
