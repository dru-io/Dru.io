<?php

namespace Drupal\feeds;

/**
 * Status of the import or clearing operation of a Feed.
 */
class State implements StateInterface {

  /**
   * Denotes the progress made.
   *
   * 0.0 meaning no progress. 1.0 = StateInterface::BATCH_COMPLETE meaning
   * finished.
   *
   * @var float
   */
  public $progress = StateInterface::BATCH_COMPLETE;

  /**
   * Used as a pointer to store where left off. Must be serializable.
   *
   * @var scalar
   */
  public $pointer;

  /**
   * The total number of items being processed.
   *
   * @var int
   */
  public $total = 0;

  /**
   * The number of Feed items created.
   *
   * @var int
   */
  public $created = 0;

  /**
   * The number of Feed items updated.
   *
   * @var int
   */
  public $updated = 0;

  /**
   * The number of Feed items deleted.
   *
   * @var int
   */
  public $deleted = 0;

  /**
   * The number of Feed items skipped.
   *
   * @var int
   */
  public $skipped = 0;

  /**
   * The number of failed Feed items.
   *
   * @var int
   */
  public $failed = 0;

  /**
   * The list of messages to display to the user.
   */
  protected $messages = [];

  /**
   * {@inheritdoc}
   */
  public function progress($total, $progress) {
    if ($progress > $total || $total === $progress) {
      $this->progress = StateInterface::BATCH_COMPLETE;
    }
    elseif ($total) {
      $this->progress = (float) ($progress / $total);
      if ($this->progress === StateInterface::BATCH_COMPLETE && $total !== $progress) {
        $this->progress = 0.99;
      }
    }
    else {
      $this->progress = StateInterface::BATCH_COMPLETE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setMessage($message = NULL, $type = 'status', $repeat = FALSE) {
    $this->messages[] = [
      'message' => $message,
      'type' => $type,
      'repeat' => $repeat,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function displayMessages() {
    foreach ($this->messages as $message) {
      drupal_set_message($message['message'], $message['type'], $message['repeat']);
    }
  }

}
