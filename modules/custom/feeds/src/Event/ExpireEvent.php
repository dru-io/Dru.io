<?php

namespace Drupal\feeds\Event;

use Drupal\feeds\FeedInterface;

/**
 * Fired to begin expiration.
 */
class ExpireEvent extends EventBase {

  /**
   * The item id being expired.
   *
   * @var int
   */
  protected $itemId;

  /**
   * Constructs an EventBase object.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   */
  public function __construct(FeedInterface $feed, $item_id) {
    $this->feed = $feed;
    $this->itemId = $item_id;
  }

  /**
   * Returns the feed item id.
   *
   * @return int
   *   The item id.
   */
  public function getItemId() {
    return $this->itemId;
  }

}
