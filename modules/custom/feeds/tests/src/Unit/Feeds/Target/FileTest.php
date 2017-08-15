<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Target;

use Drupal\feeds\Feeds\Target\File;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Target\File
 * @group feeds
 */
class FileTest extends FeedsUnitTestCase {

  protected $container;
  protected $feedType;
  protected $targetDefinition;

  public function setUp() {
    parent::setUp();

    $this->feedType = $this->getMock('Drupal\feeds\FeedTypeInterface');

    $method = $this->getMethod('Drupal\feeds\Feeds\Target\File', 'prepareTarget')->getClosure();
    $this->targetDefinition = $method($this->getMockFieldDefinition());
  }

  public function test() {
    // $configuration = [
    //   'feed_type' => $this->feedType,
    //   'target_definition' => $this->targetDefinition,
    // ];

    // $target = File::create($this->container, $configuration, 'text', []);

    // $method = $this->getProtectedClosure($target, 'prepareValue');

    // $values = ['value' => 'longstring'];
    // $method(0, $values);
    // $this->assertSame($values['value'], 'longstring');
    // $this->assertSame($values['format'], 'plain_text');
  }
}

