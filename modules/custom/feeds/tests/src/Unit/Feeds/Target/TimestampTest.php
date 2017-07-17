<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Target;

use Drupal\feeds\Feeds\Target\Timestamp;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Target\Timestamp
 * @group feeds
 */
class TimestampTest extends FeedsUnitTestCase {

  public function test() {
    $method = $this->getMethod('Drupal\feeds\Feeds\Target\Timestamp', 'prepareTarget')->getClosure();
    $target_definition = $method($this->getMockFieldDefinition());

    $configuration = [
      'feed_type' => $this->getMock('Drupal\feeds\FeedTypeInterface'),
      'target_definition' => $target_definition,
    ];
    $target = new Timestamp($configuration, 'timestamp', []);
    $method = $this->getProtectedClosure($target, 'prepareValue');

    // Test valid timestamp.
    $values = ['value' => 1411606273];
    $method(0, $values);
    $this->assertSame($values['value'], 1411606273);

    // Test year value.
    $values = ['value' => 2000];
    $method(0, $values);
    $this->assertSame($values['value'], strtotime('January 2000'));

    // Test invalid value.
    $values = ['value' => 'abc'];
    $method(0, $values);
    $this->assertSame($values['value'], '');
  }

}
