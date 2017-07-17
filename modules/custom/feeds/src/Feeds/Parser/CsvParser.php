<?php

namespace Drupal\feeds\Feeds\Parser;

use Drupal\Core\Form\FormStateInterface;
use Drupal\feeds\Component\CsvParser as CsvFileParser;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;

/**
 * Defines a CSV feed parser.
 *
 * @FeedsParser(
 *   id = "csv",
 *   title = "CSV (not working yet, do not use)",
 *   description = @Translation("Parse CSV files."),
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Parser\Form\CsvParserForm",
 *     "feed" = "Drupal\feeds\Feeds\Parser\Form\CsvParserFeedForm",
 *   },
 * )
 *
 * @todo Make mapping sources configurable, see https://www.drupal.org/node/2443471.
 */
class CsvParser extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    $feed_config = $feed->getConfigurationFor($this);

    if (!filesize($fetcher_result->getFilePath())) {
      throw new EmptyFeedException();
    }

    // Load and configure parser.
    $parser = CsvFileParser::createFromFilePath($fetcher_result->getFilePath())
      ->setDelimiter($feed_config['delimiter'] === 'TAB' ? "\t" : $feed_config['delimiter'])
      ->setHasHeader(!$feed_config['no_headers'])
      ->setStartByte((int) $state->pointer);

    // Wrap parser in a limit iterator.
    $parser = new \LimitIterator($parser, 0, $this->configuration['line_limit']);

    $header = !$feed_config['no_headers'] ? $parser->getHeader() : [];
    $result = new ParserResult();

    foreach ($parser as $row) {
      $item = new DynamicItem();

      foreach ($row as $delta => $cell) {
        $key = isset($header[$delta]) ? $header[$delta] : $delta;
        $item->set($key, $cell);
      }

      $result->addItem($item);
    }

    // Report progress.
    $state->total = filesize($fetcher_result->getFilePath());
    $state->pointer = $parser->lastLinePos();
    $state->progress($state->total, $state->pointer);

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultFeedConfiguration() {
    return [
      'delimiter' => $this->configuration['delimiter'],
      'no_headers' => $this->configuration['no_headers'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'delimiter' => ',',
      'no_headers' => 0,
      'line_limit' => 100,
    ];
  }

}
