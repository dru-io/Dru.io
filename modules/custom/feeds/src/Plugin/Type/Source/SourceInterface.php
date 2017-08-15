<?php

namespace Drupal\feeds\Plugin\Type\Source;

use Drupal\feeds\FeedInterface;
use Drupal\feeds\FeedTypeInterface;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;

/**
 * Interface for Feed sources.
 */
interface SourceInterface extends FeedsPluginInterface {

  /**
   * Adds sources to the $source array for this field.
   *
   * @param array $sources
   *   The list of sources to modify.
   * @param \Drupal\feeds\FeedTypeInterface $feed_type
   *   The feed type being added to.
   * @param array $definition
   *   The plugin definition.
   */
  public static function sources(array &$sources, FeedTypeInterface $feed_type, array $definition);

  /**
   * Returns the value for a source.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed being processed.
   * @param string $element_key
   *   The element to return a value for.
   *
   * @return array
   *   A list of scalar field values.
   */
  public function getSourceElement(FeedInterface $feed, array $item, $element_key);

}
