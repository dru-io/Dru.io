<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Parser;

use Drupal\Core\Form\FormState;
use Drupal\feeds\Feeds\Parser\CsvParser;
use Drupal\feeds\Result\FetcherResult;
use Drupal\feeds\State;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Parser\CsvParser
 * @group feeds
 */
class CsvParserTest extends FeedsUnitTestCase {

  protected $parser;
  protected $feedType;
  protected $feed;
  protected $state;

  public function setUp() {
    parent::setUp();

    $this->feedType = $this->getMock('Drupal\feeds\FeedTypeInterface');
    $configuration = ['feed_type' => $this->feedType, 'line_limit' => 3];
    $this->parser = new CsvParser($configuration, 'csv', []);
    $this->parser->setStringTranslation($this->getStringTranslationStub());

    $this->state = new State();

    $this->feed = $this->getMock('Drupal\feeds\FeedInterface');
    $this->feed->expects($this->any())
      ->method('getType')
      ->will($this->returnValue($this->feedType));
  }

  public function testFetch() {
    $this->feed->expects($this->any())
      ->method('getConfigurationFor')
      ->with($this->parser)
      ->will($this->returnValue($this->parser->defaultFeedConfiguration()));

    $file = dirname(dirname(dirname(dirname(__DIR__)))) . '/resources/example.csv';
    $fetcher_result = new FetcherResult($file);

    $result = $this->parser->parse($this->feed, $fetcher_result, $this->state);

    $this->assertSame(count($result), 3);
    $this->assertSame($result[0]->get('Header A'), '"1"');

    // Parse again. Tests batching.
    $result = $this->parser->parse($this->feed, $fetcher_result, $this->state);

    $this->assertSame(count($result), 2);
    $this->assertSame($result[0]->get('Header B'), "new\r\nline 2");
  }

  /**
   * @expectedException \Drupal\feeds\Exception\EmptyFeedException
   */
  public function testEmptyFeed() {
    touch('vfs://feeds/empty_file');
    $result = new FetcherResult('vfs://feeds/empty_file');
    $this->parser->parse($this->feed, $result, $this->state);
  }

  public function testGetMappingSources() {
    // Not really much to test here.
    $this->assertSame([], $this->parser->getMappingSources());
  }

}

