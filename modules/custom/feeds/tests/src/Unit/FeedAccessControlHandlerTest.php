<?php

namespace Drupal\Tests\feeds\Unit;

use Drupal\Core\Language\Language;
use Drupal\feeds\FeedAccessControlHandler;

/**
 * @coversDefaultClass \Drupal\feeds\FeedAccessControlHandler
 * @group feeds
 */
class FeedAccessControlHandlerTest extends FeedsUnitTestCase {

  protected $entityType;
  protected $controller;
  protected $moduleHandler;

  public function setUp() {
    parent::setUp();
    $this->entityType = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->entityType->expects($this->once())
      ->method('id')
      ->will($this->returnValue('feeds_feed'));
    $this->controller = new FeedAccessControlHandler($this->entityType);
    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->will($this->returnValue([]));
    $this->controller->setModuleHandler($this->moduleHandler);
  }

  public function test() {
    $feed = $this->getMockBuilder('\Drupal\feeds\FeedInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $feed->expects($this->any())
      ->method('bundle')
      ->will($this->returnValue('feed_bundle'));
    $feed->expects($this->any())
      ->method('language')
      ->will($this->returnValue(new Language()));

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $this->assertFalse($this->controller->access($feed, 'beep', $account));
    $this->assertFalse($this->controller->access($feed, 'unlock', $account));

    $this->controller->resetCache();

    $this->assertFalse($this->controller->access($feed, 'unlock', $account));

    $account->expects($this->any())
      ->method('hasPermission')
      ->with($this->equalTo('administer feeds'))
      ->will($this->returnValue(TRUE));

    $this->assertTrue($this->controller->access($feed, 'clear', $account));
    $this->assertTrue($this->controller->access($feed, 'view', $account));

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $account->expects($this->exactly(2))
      ->method('hasPermission')
      ->with($this->logicalOr(
           $this->equalTo('administer feeds'),
           $this->equalTo('delete feed_bundle feeds')
       ))
      ->will($this->onConsecutiveCalls(FALSE, TRUE));
    $this->assertTrue($this->controller->access($feed, 'delete', $account));
  }

  public function testCheckCreateAccess() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');

    $account->expects($this->exactly(2))
      ->method('hasPermission')
      ->with($this->logicalOr(
           $this->equalTo('administer feeds'),
           $this->equalTo('create feed_bundle feeds')
       ))
      ->will($this->onConsecutiveCalls(FALSE, FALSE));
    $this->assertFalse($this->controller->createAccess('feed_bundle', $account));

    $this->controller->resetCache();

    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->exactly(2))
      ->method('hasPermission')
      ->with($this->logicalOr(
           $this->equalTo('administer feeds'),
           $this->equalTo('create feed_bundle feeds')
       ))
      ->will($this->onConsecutiveCalls(FALSE, TRUE));
    $this->assertTrue($this->controller->createAccess('feed_bundle', $account));
  }

}
