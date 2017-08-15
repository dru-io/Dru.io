<?php

namespace Drupal\feeds\Feeds\Parser;

use Drupal\feeds\Component\GenericOpmlParser;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\OpmlItem;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;

/**
 * Defines an OPML feed parser.
 *
 * @FeedsParser(
 *   id = "opml",
 *   title = @Translation("OPML"),
 *   description = @Translation("Parse OPML files.")
 * )
 */
class OpmlParser extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    $raw = $fetcher_result->getRaw();
    if (!strlen(trim($raw))) {
      throw new EmptyFeedException();
    }

    $result = new ParserResult();

    $parser = new GenericOpmlParser($fetcher_result->getRaw());
    $opml = $parser->parse(TRUE);

    foreach ($this->getItems($opml['outlines']) as $item) {
      $item->set('feed_title', $opml['head']['#title']);
      $result->addItem($item);
    }

    return $result;
  }

  /**
   * Returns a flattened array of feed items.
   *
   * @param array $outlines
   *   A nested array of outlines.
   * @param array $categories
   *   For internal use only.
   *
   * @return array
   *   The flattened list of feed items.
   */
  protected function getItems(array $outlines, array $categories = []) {
    $items = [];

    foreach ($outlines as $outline) {
      // PHPunit is being weird about our array appending.
      // @codeCoverageIgnoreStart
      $outline += [
        '#title' => '',
        '#text' => '',
        '#xmlurl' => '',
        '#htmlurl' => '',
        'outlines' => [],
      ];
      // @codeCoverageIgnoreEnd

      $item = new OpmlItem();
      // Assume it is an actual feed if the URL is set.
      if ($outline['#xmlurl']) {
        $outline['#title'] ?
        $item->set('title', $outline['#title']) :
        $item->set('title', $outline['#text']);

        $item->set('categories', $categories)
             ->set('xmlurl', $outline['#xmlurl'])
             ->set('htmlurl', $outline['#htmlurl']);

        $items[] = $item;
      }

      // Get sub elements.
      if ($outline['outlines']) {
        $sub_categories = array_merge($categories, [$outline['#text']]);
        $items = array_merge($items, $this->getItems($outline['outlines'], $sub_categories));
      }
    }

    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [
      'feed_title' => [
        'label' => $this->t('Feed: Title of the OPML file'),
        'description' => $this->t('Title of the feed.'),
      ],
      'title' => [
        'label' => $this->t('Title'),
        'description' => $this->t('Title of the feed.'),
        'suggestions' => [
          'targets' => ['subject', 'title', 'label', 'name'],
          'types' => [
            'field_item:text' => [],
          ],
        ],
      ],
      'xmlurl' => [
        'label' => $this->t('URL'),
        'description' => $this->t('URL of the feed.'),
        'suggestions' => [
          'targets' => ['url'],
        ],
      ],
      'categories' => [
        'label' => $this->t('Categories'),
        'description' => $this->t('The categories of the feed.'),
        'suggestions' => [
          'targets' => ['field_tags'],
          'types' => [
            'field_item:taxonomy_term_reference' => [],
          ],
        ],
      ],
      'htmlurl' => [
        'label' => $this->t('Site URL'),
        'description' => $this->t('The URL of the site that provides the feed.'),
      ],
    ];
  }

}
