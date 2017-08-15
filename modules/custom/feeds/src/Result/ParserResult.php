<?php

namespace Drupal\feeds\Result;

use Drupal\feeds\Feeds\Item\ItemInterface;

/**
 * The result of a parsing stage.
 */
class ParserResult extends \SplDoublyLinkedList implements ParserResultInterface {

  /**
   * {@inheritdoc}
   */
  public function addItem(ItemInterface $item) {
    $this->push($item);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addItems(array $items) {
    foreach ($items as $item) {
      $this->push($item);
    }

    return $this;
  }

}
