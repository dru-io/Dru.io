<?php

namespace Drupal\feeds\Result;

use Drupal\feeds\Feeds\Item\ItemInterface;

/**
 * The result of a parsing stage.
 */
interface ParserResultInterface extends \Iterator, \ArrayAccess, \Countable {

  /**
   * Adds an item to the result.
   *
   * @param \Drupal\feeds\Feeds\Item\ItemInterface $item
   *   A parsed feed item.
   *
   * @return $this
   */
  public function addItem(ItemInterface $item);

  /**
   * Adds a list of items to the result.
   *
   * @param \Drupal\feeds\Feeds\Item\ItemInterface[] $items
   *   A list of feed items.
   *
   * @return $this
   */
  public function addItems(array $items);

}
