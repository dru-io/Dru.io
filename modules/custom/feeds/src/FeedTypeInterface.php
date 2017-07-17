<?php

namespace Drupal\feeds;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a feeds feed type entity.
 *
 * A feed type is a wrapper around a set of configured plugins that are used to
 * perform an import. The feed type manages the configuration on behalf of the
 * plugins.
 */
interface FeedTypeInterface extends ConfigEntityInterface {

  /**
   * Indicates that a feed should never be scheduled.
   */
  const SCHEDULE_NEVER = -1;

  /**
   * Returns the description of the feed type.
   *
   * @return string
   *   The description of the feed type.
   */
  public function getDescription();

  /**
   * Returns the import period.
   *
   * @return int
   *   The import period in seconds.
   */
  public function getImportPeriod();

  /**
   * Sets the import period.
   *
   * @param int $import_period
   *   The import period in seconds.
   */
  public function setImportPeriod($import_period);

  /**
   * Returns the configured plugins for this feed type.
   *
   * @return \Drupal\feeds\Plugin\Type\PluginBase[]
   *   An array of plugins keyed by plugin type.
   */
  public function getPlugins();

  /**
   * Returns the configured fetcher for this feed type.
   *
   * @return \Drupal\feeds\Plugin\Type\Fetcher\FetcherInterface
   *   The fetcher associated with this feed type.
   */
  public function getFetcher();

  /**
   * Returns the configured parser for this feed type.
   *
   * @return \Drupal\feeds\Plugin\Type\Parser\ParserInterface
   *   The parser associated with this feed type.
   */
  public function getParser();

  /**
   * Returns the configured processor for this feed type.
   *
   * @return \Drupal\feeds\Plugin\Type\Processor\ProcessorInterface
   *   The processor associated with this feed type.
   */
  public function getProcessor();

  /**
   * Returns the mappings for this feed type.
   *
   * @return array
   *   The list of mappings.
   */
  public function getMappings();

  /**
   * Sets the mappings for the feed type.
   *
   * @param array $mappings
   *   A list of mappings.
   */
  public function setMappings(array $mappings);

  /**
   * Adds a mapping to the feed type.
   *
   * @param array $mapping
   *   A single mapping.
   */
  public function addMapping(array $mapping);

  /**
   * Removes a mapping from the feed type.
   *
   * @param int $delta
   *   The mapping delta to remove.
   */
  public function removeMapping($delta);

  /**
   * Removes all mappings.
   */
  public function removeMappings();

  /**
   * Returns whether the feed type is considered locked.
   *
   * @return bool
   *   True if locked, false if not.
   */
  public function isLocked();

}
