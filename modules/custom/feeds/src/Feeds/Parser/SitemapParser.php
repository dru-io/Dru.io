<?php

namespace Drupal\feeds\Feeds\Parser;

use Drupal\feeds\Component\XmlParserTrait;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\SitemapItem;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;

/**
 * Defines a SitemapXML feed parser.
 *
 * @FeedsParser(
 *   id = "sitemap",
 *   title = @Translation("Sitemap XML"),
 *   description = @Translation("Parse Sitemap XML format feeds.")
 * )
 */
class SitemapParser extends PluginBase implements ParserInterface {
  use XmlParserTrait;

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    // Set time zone to GMT for parsing dates with strtotime().
    $tz = date_default_timezone_get();
    date_default_timezone_set('GMT');

    $raw = trim($fetcher_result->getRaw());
    if (!strlen($raw)) {
      throw new EmptyFeedException();
    }

    // Yes, using a DOM parser is a bit inefficient, but will do for now.
    // @todo XML error handling.
    static::startXmlErrorHandling();
    $xml = new \SimpleXMLElement($raw);
    static::stopXmlErrorHandling();
    $result = new ParserResult();

    foreach ($xml->url as $url) {
      $item = new SitemapItem();

      $item->set('url', (string) $url->loc);
      if ($url->lastmod) {
        $item->set('lastmod', strtotime($url->lastmod));
      }
      if ($url->changefreq) {
        $item->set('changefreq', (string) $url->changefreq);
      }
      if ($url->priority) {
        $item->set('priority', (string) $url->priority);
      }

      $result->addItem($item);
    }
    date_default_timezone_set($tz);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [
      'url' => [
        'label' => $this->t('Item URL (link)'),
        'description' => $this->t('URL of the feed item.'),
        'suggestions' => [
          'targets' => ['url'],
        ],
      ],
      'lastmod' => [
        'label' => $this->t('Last modification date'),
        'description' => $this->t('Last modified date as UNIX time GMT of the feed item.'),
      ],
      'changefreq' => [
        'label' => $this->t('Change frequency'),
        'description' => $this->t('How frequently the page is likely to change.'),
      ],
      'priority' => [
        'label' => $this->t('Priority'),
        'description' => $this->t('The priority of this URL relative to other URLs on the site.'),
      ],
    ];
  }

}
