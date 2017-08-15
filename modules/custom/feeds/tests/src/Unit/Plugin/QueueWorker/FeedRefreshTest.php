<?php

namespace Drupal\Tests\feeds\Unit\Plugin\QueueWorker;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Exception\LockException;
use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\feeds\Plugin\QueueWorker\FeedRefresh;
use Drupal\feeds\Result\FetcherResult;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @coversDefaultClass \Drupal\feeds\Plugin\QueueWorker\FeedRefresh
 * @group feeds
 */
class FeedRefreshTest extends FeedsUnitTestCase {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcher
   */
  protected $dispatcher;

  /**
   * The QueueWorker plugin.
   *
   * @var Drupal\feeds\Plugin\QueueWorker\FeedRefresh
   */
  protected $plugin;

  /**
   * The feed.
   *
   * @var Drupal\feeds\FeedInterface
   */
  protected $feed;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $container = new ContainerBuilder();
    $this->dispatcher = new EventDispatcher();
    $queue_factory = $this->getMock('Drupal\Core\Queue\QueueFactory', [], [], '', FALSE);
    $queue_factory->expects($this->any())
      ->method('get')
      ->with('feeds_feed_refresh:')
      ->will($this->returnValue($this->getMock('Drupal\Core\Queue\QueueInterface')));

    $container->set('queue', $queue_factory);
    $container->set('event_dispatcher', $this->dispatcher);
    $container->set('account_switcher', $this->getMockedAccountSwitcher());

    $this->plugin = FeedRefresh::create($container, [], 'feeds_feed_refresh', []);
    $this->feed = $this->getMockFeed();
  }

  /**
   * Tests initiating an import.
   */
  public function testBeginStage() {
    $this->plugin->processItem(NULL);
    $this->plugin->processItem([$this->feed, FeedRefresh::BEGIN, []]);
  }

  /**
   * Tests that an import cannot start when the feed is locked.
   */
  public function testLockException() {
    $this->feed->expects($this->once())
      ->method('lock')
      ->will($this->throwException(new LockException()));
    $this->plugin->processItem([$this->feed, FeedRefresh::BEGIN, []]);
  }

  /**
   * Tests resuming an import.
   *
   * @todo more testing?
   */
  public function testResumeStage() {
    $this->plugin->processItem([$this->feed, FeedRefresh::RESUME, []]);
  }

  /**
   * Tests that a fetch event is dispatched when initiating an import.
   *
   * @expectedException \RuntimeException
   */
  public function testExceptionOnFetchEvent() {
    $this->dispatcher->addListener(FeedsEvents::FETCH, function ($parse_event) {
      throw new \RuntimeException();
    });

    $this->plugin->processItem([$this->feed, FeedRefresh::BEGIN, []]);
  }

  /**
   * Tests the parse stage of an import.
   */
  public function testParseStage() {
    $this->dispatcher->addListener(FeedsEvents::PARSE, function ($parse_event) {
      $parser_result = new ParserResult();
      $parser_result->addItem(new DynamicItem());
      $parse_event->setParserResult($parser_result);
    });

    $fetcher_result = new FetcherResult('');

    $this->plugin->processItem([
      $this->feed,
      FeedRefresh::PARSE, [
        'fetcher_result' => $fetcher_result,
      ],
    ]);
  }

  /**
   * Tests that a parse event is dispatched when a queue task at the parse stage runs.
   *
   * @expectedException \RuntimeException
   */
  public function testExceptionOnParseEvent() {
    $this->dispatcher->addListener(FeedsEvents::PARSE, function ($parse_event) {
      throw new \RuntimeException();
    });

    $this->plugin->processItem([
      $this->feed,
      FeedRefresh::PARSE, [
        'fetcher_result' => new FetcherResult(''),
      ],
    ]);
  }

  /**
   * Tests the process stage of an import.
   */
  public function testProcessStage() {
    $this->plugin->processItem([
      $this->feed,
      FeedRefresh::PROCESS, [
        'item' => new DynamicItem(),
      ],
    ]);
  }

  /**
   * Tests that a process event is dispatched when a queue task at the process stage runs.
   *
   * @expectedException \RuntimeException
   */
  public function testExceptionOnProcessEvent() {
    $this->dispatcher->addListener(FeedsEvents::PROCESS, function ($parse_event) {
      throw new \RuntimeException();
    });

    $this->plugin->processItem([
      $this->feed,
      FeedRefresh::PROCESS, [
        'item' => new DynamicItem(),
      ],
    ]);
  }

  /**
   * Tests the final stage of an import.
   */
  public function testFinalPass() {
    $this->plugin->processItem([
      $this->feed,
      FeedRefresh::FINISH, [
        'fetcher_result' => new FetcherResult(''),
      ],
    ]);

    $this->feed->expects($this->exactly(2))
      ->method('progressParsing')
      ->will($this->returnValue(StateInterface::BATCH_COMPLETE));

    $this->plugin->processItem([
      $this->feed,
      FeedRefresh::FINISH, [
        'fetcher_result' => new FetcherResult(''),
      ],
    ]);
    $this->feed->expects($this->once())
      ->method('progressFetching')
      ->will($this->returnValue(StateInterface::BATCH_COMPLETE));
    $this->plugin->processItem([
      $this->feed,
      FeedRefresh::FINISH, [
        'fetcher_result' => new FetcherResult(''),
      ],
    ]);
  }

}
