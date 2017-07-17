<?php

namespace Drupal\Tests\feeds\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * @coversDefaultClass \Drupal\feeds\FeedHandlerBase
 * @group feeds
 */
class FeedHandlerBaseTest extends FeedsUnitTestCase {

  public function test() {
    $container = new ContainerBuilder();
    $container->set('event_dispatcher', $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'));

    $mock = $this->getMockForAbstractClass('Drupal\feeds\FeedHandlerBase', [], '', FALSE);
    $mock_class = get_class($mock);
    $hander = $mock_class::createInstance($container, $this->getMock('Drupal\Core\Entity\EntityTypeInterface'));
  }

}
