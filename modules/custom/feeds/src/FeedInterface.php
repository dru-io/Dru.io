<?php

namespace Drupal\feeds;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a feeds_feed entity.
 */
interface FeedInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Represents an active feed.
   *
   * @var int
   */
  const ACTIVE = 1;

  /**
   * Represents an inactive feed.
   *
   * @var int
   */
  const INACTIVE = 0;

  /**
   * Returns the source of the feed.
   *
   * @return string
   *   The source of a feed.
   */
  public function getSource();

  /**
   * Sets the feed source.
   *
   * @param string $source
   *   The feed source.
   */
  public function setSource($source);

  /**
   * Returns the feed type object that this feed is expected to be used with.
   *
   * @return \Drupal\feeds\FeedTypeInterface
   *   The feed type object.
   */
  public function getType();

  /**
   * Returns the feed creation timestamp.
   *
   * @return int
   *   Creation timestamp of the feed.
   */
  public function getCreatedTime();

  /**
   * Sets the feed creation timestamp.
   *
   * @param int $timestamp
   *   The feed creation timestamp.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the feed imported timestamp.
   *
   * @return int
   *   Creation timestamp of the feed.
   */
  public function getImportedTime();

  /**
   * Returns the next time the feed will be imported.
   *
   * @return int
   *   The next time the feed will be imported as a UNIX timestamp.
   */
  public function getNextImportTime();

  /**
   * Returns the time when this feed was queued for refresh, 0 if not queued.
   *
   * @return int
   *   The timestamp of the last refresh.
   */
  public function getQueuedTime();

  /**
   * Sets the time when this feed was queued for refresh, 0 if not queued.
   *
   * @param int $queued
   *   The timestamp of the last refresh.
   */
  public function setQueuedTime($queued);

  /**
   * Starts importing a feed via the batch API.
   *
   * @throws \Exception
   *   Thrown if an un-recoverable error has occurred.
   */
  public function startBatchImport();

  /**
   * Starts importing a feed via cron.
   *
   * @throws \Exception
   *   Thrown if an un-recoverable error has occurred.
   */
  public function startCronImport();

  /**
   * Start deleting all imported items of a feed via the batch API.
   *
   * @throws \Exception
   *   If processing in background is enabled, the first batch chunk of the
   *   clear task will be executed on the current page request.
   */
  public function startBatchClear();

  /**
   * Imports a raw string.
   *
   * This does not batch. It assumes that the input is small enough to not need
   * it.
   *
   * @param string $raw
   *   (optional) A raw string to import.
   *
   * @throws \Exception
   *   Re-throws any exception that bubbles up.
   *
   * @todo We need to create a job for this that will run immediately so that
   *   services don't have to wait for us to process. Can we spawn a background
   *   process?
   */
  public function pushImport($raw);

  /**
   * Removes all expired items from a feed via batch api.
   *
   * @throws \Exception
   *   Re-throws any exception that bubbles up.
   */
  public function startBatchExpire();

  /**
   * Cleans up after an import.
   */
  public function finishImport();

  /**
   * Cleans up after feed items have been delted.
   */
  public function finishClear();

  /**
   * Reports the progress of the fetching stage.
   *
   * @return float
   *   A float between 0 and 1. 1 = StateInterface::BATCH_COMPLETE.
   */
  public function progressFetching();

  /**
   * Reports the progress of the parsing stage.
   *
   * @return float
   *   A float between 0 and 1. 1 = StateInterface::BATCH_COMPLETE.
   */
  public function progressParsing();

  /**
   * Reports the progress of the import process.
   *
   * @return float
   *   A float between 0 and 1. 1 = StateInterface::BATCH_COMPLETE.
   */
  public function progressImporting();

  /**
   * Reports progress on clearing.
   */
  public function progressClearing();

  /**
   * Reports progress on expiry.
   */
  public function progressExpiring();

  /**
   * Returns a state object for a given stage.
   *
   * Lazily instantiates new states.
   *
   * @param string $stage
   *   One of StateInterface::FETCH, StateInterface::PARSE,
   *   StateInterface::PROCESS or StateInterface::CLEAR.
   *
   * @return \Drupal\feeds\StateInterface
   *   The State object for the given stage.
   */
  public function getState($stage);

  /**
   * @todo
   */
  public function setState($stage, $state);

  /**
   * @todo
   */
  public function clearStates();

  /**
   * @todo
   */
  public function saveStates();

  /**
   * Counts items imported by this feed.
   *
   * @return int
   *   The number of items imported by this Feed.
   */
  public function getItemCount();

  /**
   * Returns the configuration for a specific client plugin.
   *
   * @param \Drupal\feeds\Plugin\Type\FeedsPluginInterface $client
   *   A Feeds plugin.
   *
   * @return array
   *   The plugin configuration being managed by this Feed.
   */
  public function getConfigurationFor(FeedsPluginInterface $client);

  /**
   * Sets the configuration for a specific client plugin.
   *
   * @param \Drupal\feeds\Plugin\Type\FeedsPluginInterface $client
   *   A Feeds plugin.
   * @param array $config
   *   The configuration for the plugin.
   *
   * @todo Refactor this. This can cause conflicts if different plugin types
   *   use the same id.
   */
  public function setConfigurationFor(FeedsPluginInterface $client, array $config);

  /**
   * Returns the feed active status.
   *
   * Inactive feeds do not get imported.
   *
   * @return bool
   *   Tur if the feed is active.
   */
  public function isActive();

  /**
   * Sets the active status of a feed.
   *
   * @param bool $active
   *   True to set this feed to active, false to set it to inactive.
   */
  public function setActive($active);

  /**
   * Locks a feed.
   *
   * @throws \Drupal\feeds\Exception\LockException
   *   Thrown if the lock is unavailable.
   */
  public function lock();

  /**
   * Unlocks a feed.
   */
  public function unlock();

  /**
   * Checks whether a feed is locked.
   *
   * @return bool
   *   Returns true if the feed is locked, and false if not.
   */
  public function isLocked();

}
