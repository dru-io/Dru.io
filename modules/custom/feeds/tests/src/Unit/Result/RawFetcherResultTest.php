<?php

namespace Drupal\Tests\feeds\Unit\Result;

use Drupal\feeds\Result\RawFetcherResult;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Result\RawFetcherResult
 * @group feeds
 */
class RawFetcherResultTest extends FeedsUnitTestCase {

  public function testRaw() {
    $result = new RawFetcherResult('raw text');
    $this->assertSame($result->getRaw(), 'raw text');
  }

  public function testFilePath() {
    $result = new RawFetcherResult('raw text');
    $this->assertSame(file_get_contents($result->getFilePath()), 'raw text');

    // Call again to see if exception is thrown.
    $this->assertSame(file_get_contents($result->getFilePath()), 'raw text');
  }

}

