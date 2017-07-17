<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Target;

use Drupal\feeds\Feeds\Target\StringTarget;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Target\StringTarget
 * @group feeds
 */
class StringTargetTest extends FeedsUnitTestCase {

  public function testPrepareValue() {
    $method = $this->getMethod('Drupal\feeds\Feeds\Target\StringTarget', 'prepareTarget')->getClosure();
    $field_definition = $this->getMockFieldDefinition(['max_length' => 5]);
    $field_definition->expects($this->any())
      ->method('getType')
      ->will($this->returnValue('string'));
    $configuration = [
      'feed_type' => $this->getMock('Drupal\feeds\FeedTypeInterface'),
      'target_definition' =>  $method($field_definition),
    ];
    $target = new StringTarget($configuration, 'link', []);

    $method = $this->getProtectedClosure($target, 'prepareValue');

    $values = ['value' => 'longstring'];
    $method(0, $values);
    $this->assertSame($values['value'], 'longs');
  }

}
