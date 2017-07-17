<?php

namespace Drupal\Tests\feeds\Unit\Event;

use Drupal\feeds\Event\ProcessEvent;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Event\ProcessEvent
 * @group feeds
 */
class ProcessEventTest extends FeedsUnitTestCase {

  public function test() {
    $feed = $this->getMock('Drupal\feeds\FeedInterface');
    $item = $this->getMock('Drupal\feeds\Feeds\Item\ItemInterface');
    $event = new ProcessEvent($feed, $item);

    $this->assertSame($item, $event->getParserResult());
  }

}
