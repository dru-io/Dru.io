<?php

namespace Drupal\feeds\Event;

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResultInterface;

/**
 * Fired to begin parsing.
 */
class ParseEvent extends EventBase {

  /**
   * The fetcher result.
   *
   * @var \Drupal\feeds\Result\FetcherResultInterface
   */
  protected $fetcherResult;

  /**
   * The parser result.
   *
   * @var \Drupal\feeds\Result\ParserResultInterface
   */
  protected $parserResult;

  /**
   * Constructs a ParseEvent object.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   * @param \Drupal\feeds\Result\FetcherResultInterface $fetcher_result
   *   The fetcher result.
   */
  public function __construct(FeedInterface $feed, FetcherResultInterface $fetcher_result) {
    $this->feed = $feed;
    $this->fetcherResult = $fetcher_result;
  }

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
   * Returns the parser result.
   *
   * @return \Drupal\feeds\Result\ParserResultInterface
   *   The parser result.
   */
  public function getParserResult() {
    return $this->parserResult;
  }

  /**
   * Sets the parser result.
   *
   * @param \Drupal\feeds\Result\ParserResultInterface $result
   *   The parser result.
   */
  public function setParserResult(ParserResultInterface $result) {
    $this->parserResult = $result;
  }

}
