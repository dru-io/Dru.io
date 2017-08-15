<?php

namespace Drupal\feeds\Plugin\Type;

use Drupal\Core\Plugin\Factory\ContainerFactory;

/**
 * Plugin factory which uses the plugin definition to find arguments.
 */
class FeedsAnnotationFactory extends ContainerFactory {

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $definition = $this->discovery->getDefinition($plugin_id);

    if (empty($definition['arguments'])) {
      return parent::createInstance($plugin_id, $configuration);
    }

    // Find arguments from the annotation.
    $arguments = [$configuration, $plugin_id, $definition];
    foreach ($definition['arguments'] as $argument) {
      if (substr($argument, 0, 1) === '@') {
        $arguments[] = \Drupal::service(substr($argument, 1));
      }
      elseif (substr($argument, 0, 1) === '%' && substr($argument, -1) === '%') {
        $arguments[] = \Drupal::getContainer()->getParameter(substr($argument, 1, -1));
      }
      else {
        $arguments[] = $argument;
      }
    }

    $ref_class = new \ReflectionClass(static::getPluginClass($plugin_id, $definition, $this->interface));
    return $ref_class->newInstanceArgs($arguments);
  }

}
