<?php

namespace Drupal\Tests\feeds\Unit\Component;

use Drupal\feeds\Component\CsvParser;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @group feeds
 * @coversDefaultClass \Drupal\feeds\Component\CsvParser
 */
class CsvParserTest extends FeedsUnitTestCase {

  /**
   * @dataProvider provider
   */
  public function testAlternateLineEnding(array $expected, $ending) {
    $text = file_get_contents(dirname(dirname(dirname(dirname(__DIR__)))) . '/tests/resources/example.csv');
    $text = str_replace("\r\n", $ending, $text);

    $parser = new \LimitIterator(CsvParser::createFromString($text), 0, 4);

    $first = array_slice($expected, 0, 4);
    $this->assertSame(count(iterator_to_array($parser)), 4);
    $this->assertSame(count(iterator_to_array($parser)), 4);

    foreach ($parser as $delta => $row) {
      $this->assertSame($first[$delta], $row);
    }

    // Test second batch.
    $last_pos = $parser->lastLinePos();

    $parser = (new \LimitIterator(CsvParser::createFromString($text), 0, 4))->setStartByte($last_pos);

    $second = array_slice($expected, 4);

    // // Test that rewinding works as expected.
    $this->assertSame(2, count(iterator_to_array($parser)));
    $this->assertSame(2, count(iterator_to_array($parser)));
    foreach ($parser as $delta => $row) {
      $this->assertSame($second[$delta], $row);
    }
  }

  public function provider() {
    $expected = [
      ['Header A', 'Header B', 'Header C'],
      ['"1"', '"2"', '"3"'],
      ['qu"ote', 'qu"ote', 'qu"ote'],
      ["\r\n\r\nline1", "\r\n\r\nline2", "\r\n\r\nline3"],
      ["new\r\nline 1", "new\r\nline 2", "new\r\nline 3"],
      ["\r\n\r\nline1\r\n\r\n", "\r\n\r\nline2\r\n\r\n", "\r\n\r\nline3\r\n\r\n"],
    ];

    $unix = $expected;
    array_walk_recursive($unix, function (&$item, $key) {
      $item = str_replace("\r\n", "\n", $item);
    });

    $mac = $expected;
    array_walk_recursive($mac, function (&$item, $key) {
      $item = str_replace("\r\n", "\r", $item);
    });

    return [
      [$expected, "\r\n"],
      [$unix, "\n"],
      [$mac, "\r"],
    ];
  }

  public function testHasHeader() {
    $file = dirname(dirname(dirname(dirname(__DIR__)))) . '/tests/resources/example.csv';
    $parser = CsvParser::createFromFilePath($file)->setHasHeader();

    $this->assertSame(count(iterator_to_array($parser)), 5);
    $this->assertSame(['Header A', 'Header B', 'Header C'], $parser->getHeader());
  }

  public function  testAlternateSeparator() {
    // This implicitly tests lines without a newline.
    $parser = CsvParser::createFromString("a*b*c")
      ->setDelimiter('*');

    $this->assertSame(['a', 'b', 'c'], iterator_to_array($parser)[0]);
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testInvalidFilePath() {
    CsvParser::createFromFilePath('beep boop');
  }

  /**
   * @expectedException \InvalidArgumentException
   */
  public function testInvalidResourcePath() {
    new CsvParser('beep boop');
  }

  /**
   * @dataProvider csvFileProvider
   */
  public function testCsvParsing($file, $expected) {
    $parser = CsvParser::createFromFilePath($file);
    $parser->setHasHeader();

    $header = $parser->getHeader();

    $output = [];
    $test = [];
    foreach (iterator_to_array($parser) as $row) {
      $new_row = [];
      foreach ($row as $key => $value) {
        if (isset($header[$key])) {
          $new_row[$header[$key]] = $value;
        }
      }
      $output[] = $new_row;
    }

    $this->assertSame($expected, $output);
  }

  public function csvFileProvider() {
    $path = dirname(dirname(dirname(dirname(__DIR__)))) . '/tests/resources/csvs';
    $return = [];

    foreach (glob($path . '/*.csv') as $file) {
      $json_file = $path . '/json/' . str_replace('.csv', '.json', basename($file));

      $return[] = [
        $file,
        json_decode(file_get_contents($json_file), TRUE),
      ];
    }

    return $return;
  }

}
