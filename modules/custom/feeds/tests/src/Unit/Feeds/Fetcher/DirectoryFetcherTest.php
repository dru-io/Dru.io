<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Fetcher;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Form\FormState;
use Drupal\feeds\Feeds\Fetcher\DirectoryFetcher;
use Drupal\feeds\State;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Fetcher\DirectoryFetcher
 * @group feeds
 */
class DirectoryFetcherTest extends FeedsUnitTestCase {

  protected $fetcher;
  protected $state;
  protected $feed;

  public function setUp() {
    parent::setUp();

    $feed_type = $this->getMock('Drupal\feeds\FeedTypeInterface');
    $container = new ContainerBuilder();
    $container->set('stream_wrapper_manager', $this->getMockStreamWrapperManager());
    $this->fetcher = new DirectoryFetcher(['feed_type' => $feed_type], 'directory', []);
    $this->fetcher->setStringTranslation($this->getStringTranslationStub());

    $this->state = new State();

    $this->feed = $this->getMock('Drupal\feeds\FeedInterface');
    $this->feed->expects($this->any())
      ->method('getSource')
      ->will($this->returnValue('vfs://feeds'));

    // Prepare filesystem.
    touch('vfs://feeds/test_file_1.txt');
    touch('vfs://feeds/test_file_2.txt');
    touch('vfs://feeds/test_file_3.txt');
    touch('vfs://feeds/test_file_3.mp3');
    chmod('vfs://feeds/test_file_3.txt', 0333);
    mkdir('vfs://feeds/subdir');
    touch('vfs://feeds/subdir/test_file_4.txt');
    touch('vfs://feeds/subdir/test_file_4.mp3');
  }

  public function testFetchFile() {
    $feed = $this->getMock('Drupal\feeds\FeedInterface');
    $feed->expects($this->any())
      ->method('getSource')
      ->will($this->returnValue('vfs://feeds/test_file_1.txt'));
    $result = $this->fetcher->fetch($feed, $this->state);
    $this->assertSame('vfs://feeds/test_file_1.txt', $result->getFilePath());
  }

  /**
   * @expectedException \RuntimeException
   */
  public function testFetchDir() {
    $result = $this->fetcher->fetch($this->feed, $this->state);
    $this->assertSame($this->state->total, 2);
    $this->assertSame('vfs://feeds/test_file_1.txt', $result->getFilePath());
    $this->assertSame('vfs://feeds/test_file_2.txt', $this->fetcher->fetch($this->feed, $this->state)->getFilePath());

    chmod('vfs://feeds', 0333);
    $result = $this->fetcher->fetch($this->feed, $this->state);
  }

  public function testRecursiveFetchDir() {
    $this->fetcher->setConfiguration(['recursive_scan' => TRUE]);

    $result = $this->fetcher->fetch($this->feed, $this->state);
    $this->assertSame($this->state->total, 3);
    $this->assertSame('vfs://feeds/test_file_1.txt', $result->getFilePath());
    $this->assertSame('vfs://feeds/test_file_2.txt', $this->fetcher->fetch($this->feed, $this->state)->getFilePath());
    $this->assertSame('vfs://feeds/subdir/test_file_4.txt', $this->fetcher->fetch($this->feed, $this->state)->getFilePath());
  }

  /**
   * @expectedException \Drupal\feeds\Exception\EmptyFeedException
   */
  public function testEmptyDirectory() {
    mkdir('vfs://feeds/emptydir');
    $feed = $this->getMock('Drupal\feeds\FeedInterface');
    $feed->expects($this->any())
      ->method('getSource')
      ->will($this->returnValue('vfs://feeds/emptydir'));
    $result = $this->fetcher->fetch($feed, $this->state);
  }

}
