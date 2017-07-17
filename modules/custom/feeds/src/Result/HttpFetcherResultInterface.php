<?php

namespace Drupal\feeds\Result;

/**
 * Defines the interface for result objects returned by HTTP fetchers.
 */
interface HttpFetcherResultInterface extends FetcherResultInterface {

  /**
   * Returns the headers.
   *
   * @return array
   *   The headers array.
   */
  public function getHeaders();

}
