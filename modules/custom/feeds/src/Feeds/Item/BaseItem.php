<?php

namespace Drupal\feeds\Feeds\Item;

/**
 * Defines a base item class.
 */
abstract class BaseItem implements ItemInterface {

  /**
   * {@inheritdoc}
   */
  public function get($field) {
    return isset($this->$field) ? $this->$field : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function set($field, $value) {
    $this->$field = $value;
    return $this;
  }

}
