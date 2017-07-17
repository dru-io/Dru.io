<?php

namespace Drupal\feeds\Event;

use Drupal\feeds\FeedInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Helper class for events that need a feed.
 */
abstract class EventBase extends Event {

  /**
   * The feed being imported.
   *
   * @var \Drupal\feeds\FeedInterface
   */
  protected $feed;

  /**
   * Constructs an EventBase object.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   */
  public function __construct(FeedInterface $feed) {
    $this->feed = $feed;
  }

  /**
   * Returns the feed.
   *
   * @return \Drupal\feeds\FeedInterface
   *   The feed.
   */
  public function getFeed() {
    return $this->feed;
  }

}
