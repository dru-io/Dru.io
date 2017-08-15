<?php

namespace Drupal\feeds\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command to set the window.hash.
 */
class SetHashCommand implements CommandInterface {

  /**
   * The hash into window.hash.
   *
   * @var string
   */
  protected $hash;

  /**
   * Constructs a SetHashCommand object.
   *
   * @param string $hash
   *   The hash that will be loaded into window.hash.
   */
  public function __construct($hash) {
    $this->hash = $hash;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'feedsHash',
      'hash' => $this->hash,
    ];
  }

}
