<?php

namespace Drupal\feeds\Event;

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\ItemInterface;

/**
 * Fired to begin processing.
 */
class ProcessEvent extends EventBase {

  /**
   * The parser result.
   *
   * @var \Drupal\feeds\Result\ParserResultInterface
   */
  protected $item;

  /**
   * Constructs a ProcessEvent object.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   * @param \Drupal\feeds\Feeds\Item\ItemInterface $item
   *   The item to process.
   */
  public function __construct(FeedInterface $feed, ItemInterface $item) {
    $this->feed = $feed;
    $this->item = $item;
  }

  /**
   * Returns the parser result.
   *
   * @return \Drupal\feeds\Result\ParserResultInterface
   *   The parser result.
   */
  public function getParserResult() {
    return $this->item;
  }

}
