<?php

namespace Drupal\Tests\feeds\Unit;

use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\FeedExpireHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @coversDefaultClass \Drupal\feeds\FeedExpireHandler
 * @group feeds
 */
class FeedExpireHandlerTest extends FeedsUnitTestCase {

  protected $dispatcher;
  protected $feed;

  public function setUp() {
    parent::setUp();

    $this->dispatcher = new EventDispatcher();
    $this->feed = $this->getMock('Drupal\feeds\FeedInterface');
  }

  public function test() {
    $this->assertTrue(TRUE);
  }

  // public function testExpire() {
  //   $this->feed
  //     ->expects($this->exactly(2))
  //     ->method('progressExpiring')
  //     ->will($this->onConsecutiveCalls(0.5, 1.0));
  //   $this->feed
  //     ->expects($this->once())
  //     ->method('clearStates');

  //   $handler = new FeedExpireHandler($this->dispatcher);
  //   $result = $handler->expire($this->feed);
  //   $this->assertSame($result, 0.5);
  //   $result = $handler->expire($this->feed);
  //   $this->assertSame($result, 1.0);
  // }

  /**
   * @expectedException \Exception
   */
  // public function testException() {
  //   $this->dispatcher->addListener(FeedsEvents::EXPIRE, function($event) {
  //     throw new \Exception();
  //   });

  //   $this->feed
  //     ->expects($this->once())
  //     ->method('clearStates');

  //   $handler = new FeedExpireHandler($this->dispatcher);
  //   $handler->expire($this->feed);
  // }

}
