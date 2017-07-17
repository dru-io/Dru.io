<?php

namespace Drupal\feeds\Plugin\Type;

/**
 * Interface for plugins that want to lock their configuration.
 *
 * @todo More docs.
 */
interface LockableInterface {

  /**
   * Returns whether or not this plugin is locked.
   *
   * @return bool
   *   Returns true if the plugin is locked, false if not.
   */
  public function isLocked();

}
