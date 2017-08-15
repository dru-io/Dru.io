<?php

namespace Drupal\feeds\Plugin\Type\Target;

use Drupal\Component\Plugin\ConfigurablePluginInterface;

interface ConfigurableTargetInterface extends ConfigurablePluginInterface {

  /**
   * Returns the summary for a target.
   *
   * @return string
   *   The configuration summary.
   */
  public function getSummary();

}
