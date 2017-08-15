<?php

namespace Drupal\Tests\feeds\Unit\EventSubscriber;

use Drupal\feeds\EventSubscriber\LazySubscriber;
use Drupal\feeds\Event\ClearEvent;
use Drupal\feeds\Event\ExpireEvent;
use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Event\FetchEvent;
use Drupal\feeds\Event\InitEvent;
use Drupal\feeds\Event\ParseEvent;
use Drupal\feeds\Event\ProcessEvent;
use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\feeds\Result\ParserResult;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @coversDefaultClass \Drupal\feeds\EventSubscriber\LazySubscriber
 * @group feeds
 */
class LazySubscriberTest extends FeedsUnitTestCase {

  protected $dispatcher;
  protected $explodingDispatcher;
  protected $feed;
  protected $state;
  protected $feedType;
  protected $fetcher;
  protected $parser;
  protected $processor;

  public function setUp() {
    parent::setUp();

    $this->dispatcher = new EventDispatcher();

    // Dispatcher used to verify things only get called once.
    $this->explodingDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    $this->explodingDispatcher->expects($this->any())
      ->method('addListener')
      ->will($this->throwException(new \Exception));

    $this->state = $this->getMock('Drupal\feeds\StateInterface');
    $this->feed = $this->getMock('Drupal\feeds\FeedInterface');
    $this->feed->expects($this->any())
      ->method('getState')
      ->will($this->returnValue($this->state));
    $this->feedType = $this->getMock('Drupal\feeds\FeedTypeInterface');

    $this->fetcher = $this->getMock('Drupal\feeds\Plugin\Type\Fetcher\FetcherInterface');
    $this->parser = $this->getMock('Drupal\feeds\Plugin\Type\Parser\ParserInterface');
    $this->processor = $this->getMock('Drupal\feeds\Plugin\Type\Processor\ProcessorInterface');

    $this->feed
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue($this->feedType));
  }

  public function testGetSubscribedEvents() {
    $events = LazySubscriber::getSubscribedEvents();
    $this->assertSame(3, count($events));
  }

  public function testOnInitImport() {
    $fetcher_result = $this->getMock('Drupal\feeds\Result\FetcherResultInterface');
    $parser_result = new ParserResult();
    $parser_result->addItem(new DynamicItem());

    $this->fetcher->expects($this->once())
      ->method('fetch')
      ->with($this->feed, $this->state)
      ->will($this->returnValue($fetcher_result));
    $this->parser->expects($this->once())
      ->method('parse')
      ->with($this->feed, $fetcher_result, $this->state)
      ->will($this->returnValue($parser_result));
    $this->processor->expects($this->once())
      ->method('process');

    $this->feedType->expects($this->once())
      ->method('getFetcher')
      ->will($this->returnValue($this->fetcher));
    $this->feedType->expects($this->once())
      ->method('getParser')
      ->will($this->returnValue($this->parser));
    $this->feedType->expects($this->once())
      ->method('getProcessor')
      ->will($this->returnValue($this->processor));

    $subscriber = new LazySubscriber();

    // Fetch.
    $subscriber->onInitImport(new InitEvent($this->feed, 'fetch'), FeedsEvents::INIT_IMPORT, $this->dispatcher);
    $fetch_event = $this->dispatcher->dispatch(FeedsEvents::FETCH, new FetchEvent($this->feed));
    $this->assertSame($fetcher_result, $fetch_event->getFetcherResult());

    // Parse.
    $subscriber->onInitImport(new InitEvent($this->feed, 'parse'), FeedsEvents::INIT_IMPORT, $this->dispatcher);
    $parse_event = $this->dispatcher->dispatch(FeedsEvents::PARSE, new ParseEvent($this->feed, $fetcher_result));
    $this->assertSame($parser_result, $parse_event->getParserResult());

    // Process.
    $subscriber->onInitImport(new InitEvent($this->feed, 'process'), FeedsEvents::INIT_IMPORT, $this->dispatcher);
    foreach ($parse_event->getParserResult() as $item) {
      $this->dispatcher->dispatch(FeedsEvents::PROCESS, new ProcessEvent($this->feed, $item));
    }

    // Call again.
    $subscriber->onInitImport(new InitEvent($this->feed, 'fetch'), FeedsEvents::INIT_IMPORT, $this->explodingDispatcher);
  }

  public function testOnInitClear() {
    $clearable = $this->getMock('Drupal\feeds\Plugin\Type\ClearableInterface');
    $clearable->expects($this->exactly(2))
      ->method('clear')
      ->with($this->feed);

    $this->feedType->expects($this->once())
      ->method('getPlugins')
      ->will($this->returnValue([$clearable, $this->dispatcher, $clearable]));

    $subscriber = new LazySubscriber();

    $subscriber->onInitClear(new InitEvent($this->feed), FeedsEvents::INIT_CLEAR, $this->dispatcher);
    $this->dispatcher->dispatch(FeedsEvents::CLEAR, new ClearEvent($this->feed));

    // Call again.
    $subscriber->onInitClear(new InitEvent($this->feed), FeedsEvents::INIT_CLEAR, $this->explodingDispatcher);
  }

  public function testOnInitExpire() {
    $this->feedType->expects($this->once())
      ->method('getProcessor')
      ->will($this->returnValue($this->processor));
    $this->processor->expects($this->once())
      ->method('expireItem')
      ->with($this->feed);

    $subscriber = new LazySubscriber();
    $subscriber->onInitExpire(new InitEvent($this->feed), FeedsEvents::INIT_IMPORT, $this->dispatcher);
    $this->dispatcher->dispatch(FeedsEvents::EXPIRE, new ExpireEvent($this->feed, 1234));

    // Call again.
    $subscriber->onInitExpire(new InitEvent($this->feed), FeedsEvents::INIT_IMPORT, $this->explodingDispatcher);
  }

}
