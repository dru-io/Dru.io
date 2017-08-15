<?php

namespace Drupal\feeds\Plugin;

use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\feeds\Plugin\PluginAwareInterface;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;

/**
 * Provides form discovery capabilities for plugins.
 */
class PluginFormFactory {

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * PluginFormFactory constructor.
   *
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   */
  public function __construct(ClassResolverInterface $class_resolver) {
    $this->classResolver = $class_resolver;
  }

  public function hasForm(FeedsPluginInterface $plugin, $operation) {
    $definition = $plugin->getPluginDefinition();

    if (empty($definition['form'][$operation])) {
      return FALSE;
    }

    $class = $definition['form'][$operation];

    return class_exists($class) && is_subclass_of($class, PluginFormInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance(FeedsPluginInterface $plugin, $operation) {
    $definition = $plugin->getPluginDefinition();

    // If the form specified is the plugin itself, use it directly.
    if (get_class($plugin) === ltrim($definition['form'][$operation], '\\')) {
      $form_object = $plugin;
    }
    else {
      $form_object = $this->classResolver->getInstanceFromDefinition($definition['form'][$operation]);
    }

    // Ensure the resulting object is a plugin form.
    if (!$form_object instanceof PluginFormInterface) {
      throw new \LogicException($plugin->getPluginId(), sprintf('The "%s" plugin did not specify a valid "%s" form class, must implement \Drupal\Core\Plugin\PluginFormInterface', $plugin->getPluginId(), $operation));
    }

    if ($form_object instanceof PluginAwareInterface) {
      $form_object->setPlugin($plugin);
    }

    return $form_object;
  }

}
