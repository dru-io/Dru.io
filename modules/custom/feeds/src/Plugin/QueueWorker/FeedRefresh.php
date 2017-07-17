<?php

namespace Drupal\feeds\Plugin\QueueWorker;

use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Event\FetchEvent;
use Drupal\feeds\Event\InitEvent;
use Drupal\feeds\Event\ParseEvent;
use Drupal\feeds\Event\ProcessEvent;
use Drupal\feeds\Exception\LockException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\ItemInterface;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\StateInterface;

/**
 * @QueueWorker(
 *   id = "feeds_feed_refresh",
 *   title = @Translation("Feed refresh"),
 *   cron = {"time" = 60},
 *   deriver = "Drupal\feeds\Plugin\Derivative\FeedQueueWorker"
 * )
 */
class FeedRefresh extends FeedQueueWorkerBase {

  /**
   * Parameter passed when starting a new import.
   *
   * @var string
   */
  const BEGIN = 'begin';

  /**
   * Parameter passed when continuing an import.
   *
   * @var string
   */
  const RESUME = 'resume';

  /**
   * Parameter passed when parsing.
   *
   * @var string
   */
  const PARSE = 'parse';

  /**
   * Parameter passed when processing.
   *
   * @var string
   */
  const PROCESS = 'process';

  /**
   * Parameter passed when finishing.
   *
   * @var string
   */
  const FINISH = 'finish';

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    list($feed, $stage, $params) = $data;

    if (!$feed instanceof FeedInterface) {
      return;
    }

    $switcher = $this->switchAccount($feed);

    try {
      switch ($stage) {
        case static::BEGIN:
        case static::RESUME:
          $this->import($feed, $stage);
          break;

        case static::PARSE:
          $this->doParse($feed, $params['fetcher_result']);
          break;

        case static::PROCESS:
          $this->doProcess($feed, $params['item']);
          break;

        case static::FINISH:
          $this->finish($feed, $params['fetcher_result']);
          break;
      }
    }
    catch (\Exception $exception) {
      return $this->handleException($feed, $exception);
    }
    finally {
      $switcher->switchBack();
    }
  }

  /**
   * Queues an item.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed for which to queue an item.
   * @param string $stage
   *   The stage of importing.
   * @param [] $params
   *   Additional parameters.
   */
  protected function queueItem(FeedInterface $feed, $stage, $params = []) {
    $this->queueFactory->get('feeds_feed_refresh:' . $feed->bundle())
      ->createItem([$feed, $stage, $params]);
  }

  /**
   * Begin or resume an import.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed to perform an import on.
   * @param string $stage
   *   The stage of importing.
   */
  protected function import(FeedInterface $feed, $stage) {
    if ($stage === static::BEGIN) {
      try {
        $feed->lock();
      }
      catch (LockException $e) {
        return;
      }

      $feed->clearStates();
    }

    $this->dispatchEvent(FeedsEvents::INIT_IMPORT, new InitEvent($feed, 'fetch'));
    $fetch_event = $this->dispatchEvent(FeedsEvents::FETCH, new FetchEvent($feed));
    $feed->setState(StateInterface::PARSE, NULL);

    $feed->saveStates();
    $this->queueItem($feed, static::PARSE, [
      'fetcher_result' => $fetch_event->getFetcherResult(),
    ]);
  }

  /**
   * Parses.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed to perform a parse event on.
   * @param \Drupal\feeds\Result\FetcherResultInterface
   *   The fetcher result.
   */
  protected function doParse(FeedInterface $feed, FetcherResultInterface $fetcher_result) {
    $this->dispatchEvent(FeedsEvents::INIT_IMPORT, new InitEvent($feed, 'parse'));
    $parse_event = $this->dispatchEvent(FeedsEvents::PARSE, new ParseEvent($feed, $fetcher_result));

    $feed->saveStates();

    foreach ($parse_event->getParserResult() as $item) {
      $this->queueItem($feed, static::PROCESS, [
        'item' => $item,
      ]);
    }

    // Add a final queue item that finalizes the import.
    $this->queueItem($feed, static::FINISH, [
      'fetcher_result' => $fetcher_result,
    ]);
  }

  /**
   * Processes an item.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed to perform a process event on.
   * @param \Drupal\feeds\Feeds\Item\ItemInterface
   *   The item to import.
   */
  protected function doProcess(FeedInterface $feed, ItemInterface $item) {
    $this->dispatchEvent(FeedsEvents::INIT_IMPORT, new InitEvent($feed, 'process'));
    $this->dispatchEvent(FeedsEvents::PROCESS, new ProcessEvent($feed, $item));

    $feed->saveStates();
  }

  /**
   * Finalizes the import.
   */
  protected function finish(FeedInterface $feed, FetcherResultInterface $fetcher_result) {
    // Update item count.
    $feed->save();

    if ($feed->progressParsing() !== StateInterface::BATCH_COMPLETE) {
      $this->queueItem($feed, static::PARSE, [
        'fetcher_result' => $fetcher_result,
      ]);
    }
    elseif ($feed->progressFetching() !== StateInterface::BATCH_COMPLETE) {
      $this->queueItem($feed, static::RESUME);
    }
    else {
      $feed->finishImport();
    }
  }

}
