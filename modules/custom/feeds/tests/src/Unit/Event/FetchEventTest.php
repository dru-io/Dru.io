<?php

namespace Drupal\Tests\feeds\Unit\Event;

use Drupal\feeds\Event\FetchEvent;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Event\FetchEvent
 * @group feeds
 */
class FetchEventTest extends FeedsUnitTestCase {

  public function test() {
    $feed = $this->getMock('Drupal\feeds\FeedInterface');
    $result = $this->getMock('Drupal\feeds\Result\FetcherResultInterface');
    $event = new FetchEvent($feed);

    $event->setFetcherResult($result);
    $this->assertSame($result, $event->getFetcherResult());
  }

}
