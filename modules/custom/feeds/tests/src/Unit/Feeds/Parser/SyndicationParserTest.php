<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Parser;

use Drupal\Component\Bridge\ZfExtensionManagerSfContainer;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\feeds\Feeds\Parser\SyndicationParser;
use Drupal\feeds\Result\RawFetcherResult;
use Drupal\feeds\State;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Parser\SyndicationParser
 * @group feeds
 */
class SyndicationParserTest extends FeedsUnitTestCase {

  protected $parser;
  protected $feedType;
  protected $feed;
  protected $state;

  protected $readerExtensions = [
    'feed.reader.dublincoreentry' => 'Zend\Feed\Reader\Extension\DublinCore\Entry',
    'feed.reader.dublincorefeed' => 'Zend\Feed\Reader\Extension\DublinCore\Feed',
    'feed.reader.contententry' => 'Zend\Feed\Reader\Extension\Content\Entry',
    'feed.reader.atomentry' => 'Zend\Feed\Reader\Extension\Atom\Entry',
    'feed.reader.atomfeed' => 'Zend\Feed\Reader\Extension\Atom\Feed',
    'feed.reader.slashentry' => 'Zend\Feed\Reader\Extension\Slash\Entry',
    'feed.reader.wellformedwebentry' => 'Zend\Feed\Reader\Extension\WellFormedWeb\Entry',
    'feed.reader.threadentry' => 'Zend\Feed\Reader\Extension\Thread\Entry',
    'feed.reader.podcastentry' => 'Zend\Feed\Reader\Extension\Podcast\Entry',
    'feed.reader.podcastfeed' => 'Zend\Feed\Reader\Extension\Podcast\Feed',
    'feed.reader.georssentry' => 'Drupal\feeds\Zend\Extension\Georss\Entry',
  ];

  public function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();
    $manager = new ZfExtensionManagerSfContainer('feed.reader.');

    foreach ($this->readerExtensions as $key => $class) {
      $container->set($key, new $class());
    }

    $manager->setContainer($container);
    $container->set('feed.bridge.reader', $manager);
    \Drupal::setContainer($container);

    $this->feedType = $this->getMock('Drupal\feeds\FeedTypeInterface');
    $configuration = ['feed_type' => $this->feedType];
    $this->parser = new SyndicationParser($configuration, 'syndication', []);
    $this->parser->setStringTranslation($this->getStringTranslationStub());

    $this->state = new State();

    $this->feed = $this->getMock('Drupal\feeds\FeedInterface');
    $this->feed->expects($this->any())
      ->method('getType')
      ->will($this->returnValue($this->feedType));
  }

  public function testFetch() {
    $file = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/tests/resources/googlenewstz.rss2';
    $fetcher_result = new RawFetcherResult(file_get_contents($file));

    $result = $this->parser->parse($this->feed, $fetcher_result, $this->state);
    $this->assertSame(count($result), 6);
    $this->assertSame($result[0]->get('author_name'), 'Person Name');
    $this->assertSame($result[3]->get('title'), 'NEWSMAKER-New Japan finance minister a fiery battler - Reuters');
  }

  /**
   * @expectedException \RuntimeException
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
    $this->assertSame(count($this->parser->getMappingSources()), 16);
  }

}

