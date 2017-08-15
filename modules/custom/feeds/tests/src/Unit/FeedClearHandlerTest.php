<?php

namespace Drupal\Tests\feeds\Unit;

use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\FeedClearHandler;
use Drupal\feeds\State;
use Drupal\feeds\StateInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @coversDefaultClass \Drupal\feeds\FeedClearHandler
 * @group feeds
 */
class FeedClearHandlerTest extends FeedsUnitTestCase {

  protected $dispatcher;
  protected $feed;
  protected $context;

  public function setUp() {
    parent::setUp();

    $this->dispatcher = new EventDispatcher();
    $this->context = [];
    $this->handler = new FeedClearHandler($this->dispatcher);
    $this->handler->setStringTranslation($this->getStringTranslationStub());

    $state = new State();

    $this->feed = $this->getMock('Drupal\feeds\FeedInterface');
    $this->feed->expects($this->any())
      ->method('getState')
      ->with(StateInterface::CLEAR)
      ->will($this->returnValue($state));
  }

  public function testStartBatchClear() {
    $this->feed
      ->expects($this->once())
      ->method('lock')
      ->will($this->returnValue($this->feed));

    $this->handler->startBatchClear($this->feed);
  }

  public function testClear() {
    $this->feed->expects($this->exactly(2))
      ->method('progressClearing')
      ->will($this->onConsecutiveCalls(0.5, 1.0));

    $this->handler->clear($this->feed, $this->context);
    $this->assertSame($this->context['finished'], 0.5);
    $this->handler->clear($this->feed, $this->context);
    $this->assertSame($this->context['finished'], 1.0);
  }

  /**
   * @expectedException \Exception
   */
  public function testException() {
    $this->dispatcher->addListener(FeedsEvents::CLEAR, function($event) {
      throw new \Exception();
    });

    $this->feed->expects($this->once())
      ->method('unlock');

    $this->handler->clear($this->feed, $this->context);
  }

}
