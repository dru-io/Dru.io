<?php

namespace Drupal\Tests\feeds\Unit\Event;

use Drupal\feeds\Event\ParseEvent;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Event\ParseEvent
 * @group feeds
 */
class ParseEventTest extends FeedsUnitTestCase {

  public function test() {
    $feed = $this->getMock('Drupal\feeds\FeedInterface');
    $fetcher_result = $this->getMock('Drupal\feeds\Result\FetcherResultInterface');
    $parser_result = $this->getMock('Drupal\feeds\Result\ParserResultInterface');
    $event = new ParseEvent($feed, $fetcher_result);

    $this->assertSame($fetcher_result, $event->getFetcherResult());

    $event->setParserResult($parser_result);
    $this->assertSame($parser_result, $event->getParserResult());
  }

}
