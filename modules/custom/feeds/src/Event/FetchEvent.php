<?php


namespace Drupal\feeds\Event;

use Drupal\feeds\Result\FetcherResultInterface;

/**
 * Fired to begin fetching.
 */
class FetchEvent extends EventBase {

  /**
   * The fetcher result.
   *
   * @var \Drupal\feeds\Result\FetcherResultInterface
   */
  protected $fetcherResult;

  /**
   * Returns the fetcher result.
   *
   * @return \Drupal\feeds\Result\FetcherResultInterface
   *   The fetcher result.
   */
  public function getFetcherResult() {
    return $this->fetcherResult;
  }

  /**
   * Sets the fetcher result.
   *
   * @param \Drupal\feeds\Result\FetcherResultInterface $result
   *   The fetcher result.
   */
  public function setFetcherResult(FetcherResultInterface $result) {
    $this->fetcherResult = $result;
  }

}
