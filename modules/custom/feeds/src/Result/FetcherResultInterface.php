<?php

namespace Drupal\feeds\Result;

/**
 * Defines the interface for result objects returned by fetcher plugins.
 */
interface FetcherResultInterface {

  /**
   * Returns the file provided by the fetcher as a string.
   *
   * @return string
   *   The raw content from the source as a string.
   *
   * @throws \RuntimeException
   *   Thrown if an unexpected problem occurred usually regarding file handling.
   */
  public function getRaw();

  /**
   * Returns the path to the file containing the file provided by the fetcher.
   *
   * When it comes to preference and efficiency, this method should be used
   * whenever possible by parsers so that they do not have to load the entire
   * file into memory.
   *
   * @return string
   *   A path to a file containing the raw content of a feed.
   *
   * @throws \RuntimeException
   *   Thrown if an unexpected problem occurred usually regarding file handling.
   */
  public function getFilePath();

}
