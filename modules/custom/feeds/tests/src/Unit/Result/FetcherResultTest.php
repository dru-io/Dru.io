<?php

namespace Drupal\Tests\feeds\Unit\Result;

use Drupal\feeds\Result\FetcherResult;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Result\FetcherResult
 * @group feeds
 */
class FetcherResultTest extends FeedsUnitTestCase {

  public function testGetRaw() {
    file_put_contents('vfs://feeds/test_file', pack('CCC', 0xef, 0xbb, 0xbf) . 'I am test data.');
    $result = new FetcherResult('vfs://feeds/test_file');
    $this->assertSame('I am test data.', $result->getRaw());
  }

  public function testGetFilePath() {
    file_put_contents('vfs://feeds/test_file', 'I am test data.');
    $result = new FetcherResult('vfs://feeds/test_file');
    $this->assertSame('vfs://feeds/test_file', $result->getFilePath());
  }

  public function testGetSanitizedFilePath() {
    file_put_contents('vfs://feeds/test_file', pack('CCC', 0xef, 0xbb, 0xbf) . 'I am test data.');
    $result = new FetcherResult('vfs://feeds/test_file');
    $this->assertSame('I am test data.', file_get_contents($result->getFilePath()));
  }

  /**
   * @expectedException \RuntimeException
   */
  public function testNonExistantFile() {
    $result = new FetcherResult('IDONOTEXIST');
    $result->getRaw();
  }

  /**
   * @expectedException \RuntimeException
   */
  public function testNonReadableFile() {
    file_put_contents('vfs://feeds/test_file', 'I am test data.');
    chmod('vfs://feeds/test_file', 000);
    $result = new FetcherResult('vfs://feeds/test_file');
    $result->getRaw();
  }

  /**
   * @expectedException \RuntimeException
   */
  public function testNonWritableFile() {
    file_put_contents('vfs://feeds/test_file', pack('CCC', 0xef, 0xbb, 0xbf) . 'I am test data.');
    chmod('vfs://feeds/test_file', 0444);
    $result = new FetcherResult('vfs://feeds/test_file');
    $result->getFilePath();
  }

}
