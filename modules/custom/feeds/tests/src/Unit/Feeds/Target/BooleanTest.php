<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Target;

use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Drupal\feeds\FeedTypeInterface;
use Drupal\feeds\Feeds\Target\Boolean;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Target\Boolean
 * @covers \Drupal\feeds\Feeds\Target\Boolean
 * @group feeds
 */
class BooleanTest extends FeedsUnitTestCase {

  public function test() {
    $method = $this->getMethod(Boolean::class, 'prepareTarget')->getClosure();

    $configuration = [
      'feed_type' => $this->getMock(FeedTypeInterface::class),
      'target_definition' =>  $method($this->getMockFieldDefinition()),
    ];

    $target = new Boolean($configuration, 'boolean', []);
    $values = ['value' => 'string'];

    $method = $this->getProtectedClosure($target, 'prepareValue');
    $method(0, $values);
    $this->assertSame(1, $values['value']);
  }

}
