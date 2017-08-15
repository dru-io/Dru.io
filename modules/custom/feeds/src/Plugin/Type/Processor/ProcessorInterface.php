<?php

namespace Drupal\feeds\Plugin\Type\Processor;

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\ItemInterface;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;
use Drupal\feeds\StateInterface;

/**
 * Interface for Feeds processor plugins.
 */
interface ProcessorInterface extends FeedsPluginInterface {

  /**
   * Skip items that exist already.
   *
   * @var int
   */
  const SKIP_EXISTING = 0;

  /**
   * Replace items that exist already.
   *
   * @var int
   */
  const REPLACE_EXISTING = 1;

  /**
   * Update items that exist already.
   *
   * @var int
   */
  const UPDATE_EXISTING = 2;

  /**
   * Feed items should never be expired.
   *
   * @var int
   */
  const EXPIRE_NEVER = -1;

  /**
   * Processes the results from a parser.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed being imported.
   * @param \Drupal\feeds\Feeds\Item\ItemInterface $item
   *   The item to process.
   * @param \Drupal\feeds\StateInterface $state
   *   The state object.
   */
  public function process(FeedInterface $feed, ItemInterface $item, StateInterface $state);

  /**
   * Called after an import is completed.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   * @param \Drupal\feeds\StateInterface $state
   *   The state object.
   */
  public function postProcess(FeedInterface $feed, StateInterface $state);

  /**
   * Deletes feed items older than REQUEST_TIME - $time.
   *
   * Do not invoke expire on a processor directly. This is called automatically
   * after an import completes.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed to expire items for.
   * @param int $time
   *   (optional) All items produced by this configuration that are older than
   *   REQUEST_TIME - $time should be deleted. If null, the processor should use
   *   internal configuration. Defaults to null.
   */
  public function getExpiredIds(FeedInterface $feed, $time = NULL);

  /**
   * Deletes feed items older than REQUEST_TIME - $time.
   *
   * Do not invoke expire on a processor directly. This is called automatically
   * after an import completes.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed to expire items for.
   * @param int $item_id
   *   The feed item id to expire.
   * @param \Drupal\feeds\StateInterface $state
   *   The state object.
   */
  public function expireItem(FeedInterface $feed, $item_id, StateInterface $state);

  /**
   * Counts the number of items imported by this processor.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed who's items we are counting.
   *
   * @return int
   *   The number of items imported by this feed.
   */
  public function getItemCount(FeedInterface $feed);

  /**
   * Returns the label for feed items created.
   *
   * @return string
   *   The item label.
   */
  public function getItemLabel();

  /**
   * Returns the plural label for feed items created.
   *
   * @return string
   *   The plural item label.
   */
  public function getItemLabelPlural();

  /**
   * Returns the age of items that should be removed.
   *
   * @return int
   *   The unix timestamp of the age of items to be removed.
   *
   * @todo Move this to a separate interface.
   */
  public function expiryTime();

}
