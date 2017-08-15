<?php

namespace Drupal\feeds;

/**
 * Status of the import or clearing operation of a Feed.
 */
interface StateInterface {

  /**
   * Batch operation complete.
   *
   * @var floar
   */
  const BATCH_COMPLETE = 1.0;

  /**
   * The start time key.
   *
   * @var string
   */
  const START = 'start_time';

  /**
   * Denotes the fetch stage.
   *
   * @var string
   */
  const FETCH = 'fetch';

  /**
   * Denotes the parse stage.
   *
   * @var string
   */
  const PARSE = 'parse';

  /**
   * Denotes the process stage.
   *
   * @var string
   */
  const PROCESS = 'process';

  /**
   * Denotes the clear stage.
   *
   * @var string
   */
  const CLEAR = 'clear';

  /**
   * Denotes the expire stage.
   *
   * @var string
   */
  const EXPIRE = 'expire';

  /**
   * Reports the progress of a batch.
   *
   * When $total === $progress, the state of the task tracked by this state is
   * regarded to be complete.
   *
   * Should handle the following cases gracefully:
   * - $total is 0.
   * - $progress is larger than $total.
   * - $progress approximates $total so that $finished rounds to 1.0.
   *
   * @param int $total
   *   A number that is the total to be worked off.
   * @param int $progress
   *   A number that is the progress made on $total.
   */
  public function progress($total, $progress);

  /**
   * Sets a message to display to the user.
   *
   * This should be used by plugins instead of drupal_set_message(). It will
   * store messages and display them at the appropriate time.
   *
   * @param string $message
   *   (optional) The translated message to be displayed to the user. For
   *   consistency with other messages, it should begin with a capital letter
   *   and end with a period.
   * @param string $type
   *   (optional) The message's type. Defaults to 'status'. These values are
   *   supported:
   *   - 'status'
   *   - 'warning'
   *   - 'error'
   * @param bool $repeat
   *   (optional) If this is FALSE and the message is already set, then the
   *   message won't be repeated. Defaults to FALSE.
   *
   * @see drupal_set_message()
   */
  public function setMessage($message = NULL, $type = 'status', $repeat = FALSE);

  /**
   * Shows the messages to the user.
   *
   * @see \Drupal\feeds\StateInterface::setMessage()
   */
  public function displayMessages();

}
