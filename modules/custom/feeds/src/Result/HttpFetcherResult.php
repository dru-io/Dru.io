<?php

namespace Drupal\feeds\Result;

/**
 * The default fetcher result object.
 */
class HttpFetcherResult extends FetcherResult implements HttpFetcherResultInterface {

  /**
   * The HTTP headers.
   *
   * @var array
   */
  protected $headers;

  /**
   * Constructs an HttpFetcherResult object.
   *
   * @param string $file_path
   *   The path to the result file.
   * @param array $headers
   *   An array of HTTP headers.
   */
  public function __construct($file_path, array $headers) {
    parent::__construct($file_path);
    $this->headers = array_change_key_case($headers);
  }

  /**
   * {@inheritdoc}
   */
  public function getHeaders() {
    return $this->headers;
  }

}
