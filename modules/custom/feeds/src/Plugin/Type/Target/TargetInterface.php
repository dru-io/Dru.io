<?php

namespace Drupal\feeds\Plugin\Type\Target;

use Drupal\Core\Entity\EntityInterface;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\FeedTypeInterface;

/**
 * Interface for Feed targets.
 */
interface TargetInterface {

  /**
   * Returns the targets defined by this plugin.
   *
   * @return array
   *   An array of targets.
   *
   * @todo Finish documenting this.
   */
  public static function targets(array &$targets, FeedTypeInterface $feed_type, array $definition);

  /**
   * Sets the values on an object.
   *
   * @param mixed $target_item
   *   The target object.
   * @param string $target
   *   The name of the target to set.
   * @param array $values
   *   A list of values to set on the target.
   */
  public function setTarget(FeedInterface $feed, EntityInterface $target_item, $target, array $values);

}
