<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Fetcher;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormState;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\FeedTypeInterface;
use Drupal\feeds\Feeds\Fetcher\HttpFetcher;
use Drupal\feeds\State;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Fetcher\HttpFetcher
 * @group feeds
 */
class HttpFetcherTest extends FeedsUnitTestCase {

  protected $feed;

  protected $fetcher;

  protected $mockHandler;

  public function setUp() {
    parent::setUp();

    $feed_type = $this->getMock(FeedTypeInterface::class);

    $this->mockHandler = new MockHandler();
    $client = new Client(['handler' => HandlerStack::create($this->mockHandler)]);
    $cache = $this->getMock(CacheBackendInterface::class);

    $file_system = $this->prophesize(FileSystemInterface::class);
    $file_system->tempnam(Argument::type('string'), Argument::type('string'))->will(function ($args) {
      return tempnam($args[0], $args[1]);
    });
    $file_system->realpath(Argument::type('string'))->will(function ($args) {
      return realpath($args[0]);
    });

    $this->fetcher = new HttpFetcher(['feed_type' => $feed_type], 'http', [], $client, $cache, $file_system->reveal());
    $this->fetcher->setStringTranslation($this->getStringTranslationStub());

    $this->feed = $this->prophesize(FeedInterface::class);
    $this->feed->id()->willReturn(1);
    $this->feed->getSource()->willReturn('http://example.com');
  }

  public function testFetch() {
    $this->mockHandler->append(new Response(200, [], 'test data'));

    $result = $this->fetcher->fetch($this->feed->reveal(), new State());
    $this->assertSame('test data', $result->getRaw());
  }

  /**
   * @expectedException \Drupal\feeds\Exception\EmptyFeedException
   */
  public function testFetch304() {
    $this->mockHandler->append(new Response(304));
    $this->fetcher->fetch($this->feed->reveal(), new State());
  }

  /**
   * @expectedException \RuntimeException
   */
  public function testFetch404() {
    $this->mockHandler->append(new Response(404));
    $this->fetcher->fetch($this->feed->reveal(), new State());
  }

  /**
   * @expectedException \RuntimeException
   */
  public function testFetchError() {
    $this->mockHandler->append(new RequestException('', new Request(200, 'http://google.com')));
    $this->fetcher->fetch($this->feed->reveal(), new State());
  }

  public function testOnFeedDeleteMultiple() {
    $feed = $this->getMock(FeedInterface::class);
    $feed->expects($this->exactly(3))
      ->method('getSource')
      ->will($this->returnValue('http://example.com'));
    $feeds = [$feed, $feed, $feed];

    $this->fetcher->onFeedDeleteMultiple($feeds, new State());
  }

}

