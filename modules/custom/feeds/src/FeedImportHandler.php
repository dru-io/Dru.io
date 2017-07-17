<?php

namespace Drupal\feeds;

use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Event\FetchEvent;
use Drupal\feeds\Event\InitEvent;
use Drupal\feeds\Event\ParseEvent;
use Drupal\feeds\Event\ProcessEvent;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\Exception\LockException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\ItemInterface;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResultInterface;
use Drupal\feeds\Result\RawFetcherResult;
use Drupal\feeds\StateInterface;

/**
 * Runs the actual import on a feed.
 */
class FeedImportHandler extends FeedHandlerBase {

  /**
   * The fetcher result.
   *
   * @var \Drupal\feeds\Result\FetcherResultInterface
   */
  protected $fetcherResult;

  /**
   * {@inheritdoc}
   */
  public function startBatchImport(FeedInterface $feed) {
    try {
      $feed->lock();
    }
    catch (LockException $e) {
      drupal_set_message(t('The feed became locked before the import could begin.'), 'warning');
      return;
    }

    $feed->clearStates();
    $this->startBatchFetch($feed);
  }

  /**
   * Sets the fetch batch.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed being fetched.
   */
  protected function startBatchFetch(FeedInterface $feed) {
    $batch = [
      'title' => $this->t('Fetching: %title', ['%title' => $feed->label()]),
      'init_message' => $this->t('Fetching: %title', ['%title' => $feed->label()]),
      'operations' => [
        [[$this, 'batchFetch'], [$feed]],
      ],
      'progress_message' => $this->t('Fetching: %title', ['%title' => $feed->label()]),
      'error_message' => $this->t('An error occored while fetching %title.', ['%title' => $feed->label()]),
    ];

    batch_set($batch);
  }

  /**
   * Performs the batch fetching.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed being fetched.
   */
  public function batchFetch(FeedInterface $feed) {
    try {
      $this->fetcherResult = $this->doFetch($feed);
    }
    catch (\Exception $exception) {
      return $this->handleException($feed, $exception);
    }

    $this->startBatchParse($feed);
    $feed->saveStates();
  }

  /**
   * Sets the parse batch.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed being fetched.
   */
  protected function startBatchParse(FeedInterface $feed) {
    $batch = [
      'title' => $this->t('Parsing: %title', ['%title' => $feed->label()]),
      'init_message' => $this->t('Parsing: %title', ['%title' => $feed->label()]),
      'operations' => [
        [[$this, 'batchParse'], [$feed]],
      ],
      'progress_message' => $this->t('Parsing: %title', ['%title' => $feed->label()]),
      'error_message' => $this->t('An error occored while parsing %title.', ['%title' => $feed->label()]),
    ];

    batch_set($batch);
  }

  /**
   * Performs the batch parsing.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   */
  public function batchParse(FeedInterface $feed) {
    try {
      $parser_result = $this->doParse($feed, $this->fetcherResult);
    }
    catch (\Exception $exception) {
      return $this->handleException($feed, $exception);
    }

    $this->startBatchProcess($feed, $parser_result);
    $feed->saveStates();
  }

  /**
   * Starts the process batch.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   * @param \Drupal\feeds\Result\ParserResultInterface $parser_result
   *   The parser result.
   */
  protected function startBatchProcess(FeedInterface $feed, ParserResultInterface $parser_result) {
    $batch = [
      'title' => $this->t('Processing: %title', ['%title' => $feed->label()]),
      'init_message' => $this->t('Processing: %title', ['%title' => $feed->label()]),
      'progress_message' => $this->t('Processing: %title', ['%title' => $feed->label()]),
      'error_message' => $this->t('An error occored while processing %title.', ['%title' => $feed->label()]),
    ];

    foreach ($parser_result as $item) {
      $batch['operations'][] = [[$this, 'batchProcess'], [$feed, $item]];
    }
    $batch['operations'][] = [[$this, 'batchPostProcess'], [$feed]];

    batch_set($batch);
  }

  /**
   * Performs the batch processing.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   * @param \Drupal\feeds\Feeds\Item\ItemInterface $item
   *   An item to process.
   */
  public function batchProcess(FeedInterface $feed, ItemInterface $item) {
    try {
      $this->doProcess($feed, $item);
    }
    catch (\Exception $exception) {
      return $this->handleException($feed, $exception);
    }

    $feed->saveStates();
  }

  /**
   * Finishes importing, or starts unfinished stages.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   */
  public function batchPostProcess(FeedInterface $feed) {
    if ($feed->progressParsing() !== StateInterface::BATCH_COMPLETE) {
      $this->startBatchParse($feed);
    }
    elseif ($feed->progressFetching() !== StateInterface::BATCH_COMPLETE) {
      $this->startBatchFetch($feed);
    }
    else {
      $feed->finishImport();
      $feed->startBatchExpire();
    }
  }

  /**
   * Handles a push import.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed receiving the push.
   * @param string $payload
   *   The feed contents.
   *
   * @todo Move this to a queue.
   */
  public function pushImport(FeedInterface $feed, $payload) {
    $feed->lock();
    $fetcher_result = new RawFetcherResult($payload);

    try {
      do {
        foreach ($this->doParse($feed, $fetcher_result) as $item) {
          $this->doProcess($feed, $item);
        }
      } while ($feed->progressImporting() !== StateInterface::BATCH_COMPLETE);
    }
    catch (EmptyFeedException $e) {
      // Not an error.
    }
    catch (\Exception $exception) {
      // Do nothing. Will throw later.
    }

    $feed->finishImport();

    if (isset($exception)) {
      throw $exception;
    }
  }

  /**
   * Invokes the fetch stage.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed to fetch.
   *
   * @return \Drupal\feeds\Result\FetcherResultInterface
   *   The result of the fetcher.
   */
  protected function doFetch(FeedInterface $feed) {
    $this->dispatchEvent(FeedsEvents::INIT_IMPORT, new InitEvent($feed, 'fetch'));
    $fetch_event = $this->dispatchEvent(FeedsEvents::FETCH, new FetchEvent($feed));
    $feed->setState(StateInterface::PARSE, NULL);

    return $fetch_event->getFetcherResult();
  }

  /**
   * Invokes the parse stage.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed to fetch.
   * @param \Drupal\feeds\Result\FetcherResultInterface $fetcher_result
   *   The result of the fetcher.
   *
   * @return \Drupal\feeds\Result\ParserResultInterface
   *   The result of the parser.
   */
  protected function doParse(FeedInterface $feed, FetcherResultInterface $fetcher_result) {
    $this->dispatchEvent(FeedsEvents::INIT_IMPORT, new InitEvent($feed, 'parse'));

    $parse_event = $this->dispatchEvent(FeedsEvents::PARSE, new ParseEvent($feed, $fetcher_result));

    return $parse_event->getParserResult();
  }

  /**
   * Invokes the process stage.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed to fetch.
   * @param \Drupal\feeds\Feeds\Item\ItemInterface $item
   *   The item to process.
   */
  protected function doProcess(FeedInterface $feed, ItemInterface $item) {
    $this->dispatchEvent(FeedsEvents::INIT_IMPORT, new InitEvent($feed, 'process'));
    $this->dispatchEvent(FeedsEvents::PROCESS, new ProcessEvent($feed, $item));
  }

  /**
   * Handles an exception during importing.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed.
   * @param \Exception $exception
   *   The exception that was thrown.
   *
   * @throws \Exception
   *   Thrown if $exception is not an instance of EmptyFeedException.
   */
  protected function handleException(FeedInterface $feed, \Exception $exception) {
    $feed->finishImport();

    if ($exception instanceof EmptyFeedException) {
      return;
    }
    if ($exception instanceof \RuntimeException) {
      drupal_set_message($exception->getMessage(), 'error');
      return;
    }

    throw $exception;
  }

}
