<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Item;

use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Item\DynamicItem
 * @group feeds
 */
class DynamicItemTest extends FeedsUnitTestCase {

  /**
   * Tests basic behavior.
   */
  public function test() {
    $item = new DynamicItem();
    $item->set('field', 'value');
    $this->assertSame($item->get('field'), 'value');
  }

}
