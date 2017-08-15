<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Target;

use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Target\Uri
 * @group feeds
 */
class UriTest extends FeedsUnitTestCase {

  public function testPrepareValue() {
    $method = $this->getMethod('Drupal\feeds\Feeds\Target\Uri', 'prepareTarget')->getClosure();
    $method($this->getMockFieldDefinition());
  }

}
