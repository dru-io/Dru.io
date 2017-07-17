<?php

namespace Drupal\feeds\Plugin;

use Drupal\feeds\Plugin\Type\FeedsPluginInterface;

/**
 * Interface for objects that are aware of a plugin.
 */
interface PluginAwareInterface {

  /**
   * Sets the plugin for this object.
   *
   * @param \Drupal\Component\Plugin\FeedsPluginInterface $plugin
   *   The plugin.
   */
  public function setPlugin(FeedsPluginInterface $plugin);

}
