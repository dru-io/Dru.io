<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Item;

use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Item\BaseItem
 * @group feeds
 */
class BaseItemTest extends FeedsUnitTestCase {

  /**
   * Tests basic behavior.
   */
  public function test() {
    $item = $this->getMockForAbstractClass('Drupal\feeds\Feeds\Item\BaseItem');
    $item->set('field', 'value');
    $this->assertSame($item->get('field'), 'value');
  }

}
