<?php

namespace Drupal\Tests\feeds\Unit;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\feeds\FeedTypeAccessControlHandler;
use Drupal\feeds\FeedTypeInterface;

/**
 * @coversDefaultClass \Drupal\feeds\FeedTypeAccessControlHandler
 * @group feeds
 */
class FeedTypeAccessControlHandlerTest extends FeedsUnitTestCase {

  public function setUp() {
    parent::setUp();

    $cache_contexts_manager = $this->prophesize(CacheContextsManager::class);
    $cache_contexts_manager->assertValidTokens()->willReturn(TRUE);
    $cache_contexts_manager->reveal();
    $container = new Container();
    $container->set('cache_contexts_manager', $cache_contexts_manager);
    \Drupal::setContainer($container);

    $this->entity = $this->prophesize(FeedTypeInterface::class);
    $this->account = $this->prophesize(AccountInterface::class);
    $this->account->hasPermission('administer feeds')->willReturn(TRUE);

    $entity_type = $this->prophesize(EntityTypeInterface::class);
    $entity_type->getAdminPermission()->willReturn('administer feeds');
    $entity_type->id()->willReturn('feed_type');
    $this->controller = new FeedTypeAccessControlHandler($entity_type->reveal());
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccess() {
    $method = $this->getMethod(FeedTypeAccessControlHandler::class, 'checkAccess');
    $result = $method->invokeArgs($this->controller, [$this->entity->reveal(), 'view', $this->account->reveal()]);
    $this->assertTrue($result->isAllowed());

    $result = $method->invokeArgs($this->controller, [$this->entity->reveal(), 'delete', $this->account->reveal()]);
    $this->assertTrue($result->isAllowed());

    $this->entity->getCacheContexts()->willReturn([]);
    $this->entity->getCacheTags()->willReturn([]);
    $this->entity->getCacheMaxAge()->willReturn(0);

    $this->entity->isLocked()->willReturn(TRUE);
    $this->entity->isNew()->willReturn(FALSE);
    $result = $method->invokeArgs($this->controller, [$this->entity->reveal(), 'delete', $this->account->reveal()]);
    $this->assertFalse($result->isAllowed());

    $this->account->hasPermission('administer feeds')->willReturn(FALSE);
    $result = $method->invokeArgs($this->controller, [$this->entity->reveal(), 'delete', $this->account->reveal()]);
    $this->assertFalse($result->isAllowed());

    $result = $method->invokeArgs($this->controller, [$this->entity->reveal(), 'view', $this->account->reveal()]);
    $this->assertFalse($result->isAllowed());

    $this->entity->isNew()->willReturn(TRUE);
    $result = $method->invokeArgs($this->controller, [$this->entity->reveal(), 'delete', $this->account->reveal()]);
    $this->assertFalse($result->isAllowed());
  }

}
