<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Parser;

use Drupal\feeds\Feeds\Parser\SitemapParser;
use Drupal\feeds\Result\RawFetcherResult;
use Drupal\feeds\State;
use Drupal\feeds\StateInterface;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Parser\SitemapParser
 * @group feeds
 */
class SitemapParserTest extends FeedsUnitTestCase {

  protected $parser;
  protected $feedType;
  protected $feed;
  protected $state;

  public function setUp() {
    parent::setUp();

    $this->feedType = $this->getMock('Drupal\feeds\FeedTypeInterface');
    $configuration = ['feed_type' => $this->feedType];
    $this->parser = new SitemapParser($configuration, 'sitemap', []);
    $this->parser->setStringTranslation($this->getStringTranslationStub());

    $this->state = new State();

    $this->feed = $this->getMock('Drupal\feeds\FeedInterface');
    $this->feed->expects($this->any())
      ->method('getType')
      ->will($this->returnValue($this->feedType));
  }

  public function testFetch() {
    $file = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/tests/resources/sitemap-example.xml';
    $fetcher_result = new RawFetcherResult(file_get_contents($file));

    $result = $this->parser->parse($this->feed, $fetcher_result, $this->state);
    $this->assertSame(count($result), 5);
    $this->assertSame($result[0]->get('url'), 'http://www.example.com/');
    $this->assertSame($result[3]->get('priority'), '0.3');
  }

  /**
   * @expectedException \Exception
   */
  public function testInvalidFeed() {
    $fetcher_result = new RawFetcherResult('beep boop');
    $result = $this->parser->parse($this->feed, $fetcher_result, $this->state);
  }

  /**
   * @expectedException \Drupal\feeds\Exception\EmptyFeedException
   */
  public function testEmptyFeed() {
    $result = new RawFetcherResult('');
    $this->parser->parse($this->feed, $result, $this->state);
  }

  public function testGetMappingSources() {
    // Not really much to test here.
    $this->assertSame(count($this->parser->getMappingSources()), 4);
  }

}

