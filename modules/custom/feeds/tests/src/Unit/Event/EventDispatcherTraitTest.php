<?php

namespace Drupal\Tests\feeds\Unit\Event;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Symfony\Component\EventDispatcher\Event;

/**
 * @coversDefaultClass \Drupal\feeds\Event\EventDispatcherTrait
 * @group feeds
 */
class EventDispatcherTraitTest extends FeedsUnitTestCase {

  public function test() {
    $mock = $this->getMockForTrait('Drupal\feeds\Event\EventDispatcherTrait');
    $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

    $container = new ContainerBuilder();
    $container->set('event_dispatcher', $dispatcher);
    \Drupal::setContainer($container);
    $method = $this->getMethod(get_class($mock), 'getEventDispatcher');
    $this->assertSame($dispatcher, $method->invokeArgs($mock, []));

    $mock->setEventDispatcher($dispatcher);
    $this->assertSame($dispatcher, $method->invokeArgs($mock, []));

    $event = new Event();
    $dispatcher->expects($this->once())
      ->method('dispatch')
      ->with('test_event', $event);
    $method = $this->getMethod(get_class($mock), 'dispatchEvent');
    $method->invokeArgs($mock, ['test_event', $event]);
  }

}
