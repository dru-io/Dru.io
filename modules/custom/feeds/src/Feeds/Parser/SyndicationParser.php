<?php

namespace Drupal\feeds\Feeds\Parser;

use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\SyndicationItem;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;
use Zend\Feed\Reader\Exception\ExceptionInterface;
use Zend\Feed\Reader\Reader;

/**
 * Defines an RSS and Atom feed parser.
 *
 * @FeedsParser(
 *   id = "syndication",
 *   title = @Translation("RSS/Atom"),
 *   description = @Translation("Default parser for RSS, Atom and RDF feeds.")
 * )
 */
class SyndicationParser extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    $result = new ParserResult();
    Reader::setExtensionManager(\Drupal::service('feed.bridge.reader'));
    Reader::registerExtension('GeoRSS');

    $raw = $fetcher_result->getRaw();
    if (!strlen(trim($raw))) {
      throw new EmptyFeedException();
    }

    try {
      $channel = Reader::importString($raw);
    }
    catch (ExceptionInterface $e) {
      $args = ['%site' => $feed->label(), '%error' => trim($e->getMessage())];
      throw new \RuntimeException($this->t('The feed from %site seems to be broken because of error "%error".', $args));
    }

    foreach ($channel as $delta => $entry) {
      $item = new SyndicationItem();
      // Move the values to an array as expected by processors.
      $item
        ->set('title', $entry->getTitle())
        ->set('guid', $entry->getId())
        ->set('url', $entry->getLink())
        ->set('guid', $entry->getId())
        ->set('url', $entry->getLink())
        ->set('description', $entry->getDescription())
        ->set('content', $entry->getContent())
        ->set('tags', $entry->getCategories()->getValues())
        ->set('feed_title', $channel->getTitle())
        ->set('feed_description', $channel->getDescription())
        ->set('feed_url', $channel->getLink());

      if ($image = $channel->getImage()) {
        $item->set('feed_image_uri', $image['uri']);
      }

      if ($enclosure = $entry->getEnclosure()) {
        $item->set('enclosures', [rawurldecode($enclosure->url)]);
      }

      if ($author = $entry->getAuthor()) {
        $author += ['name' => '', 'email' => ''];
        $item->set('author_name', $author['name'])
             ->set('author_email', $author['email']);
      }
      if ($date = $entry->getDateModified()) {
        $item->set('timestamp', $date->getTimestamp());
      }

      if ($point = $entry->getGeoPoint()) {
        $item->set('georss_lat', $point['lat'])
             ->set('georss_lon', $point['lon']);
      }

      $result->addItem($item);
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [
      'feed_title' => [
        'label' => $this->t('Feed title'),
        'description' => $this->t('Title of the feed.'),
      ],
      'feed_description' => [
        'label' => $this->t('Feed description'),
        'description' => $this->t('Description of the feed.'),
      ],
      'feed_image_uri' => [
        'label' => $this->t('Feed image'),
        'description' => $this->t('The URL of the feed image.'),
      ],
      'feed_url' => [
        'label' => $this->t('Feed URL (link)'),
        'description' => $this->t('URL of the feed.'),
      ],
      'title' => [
        'label' => $this->t('Title'),
        'description' => $this->t('Title of the feed item.'),
        'suggestions' => [
          'targets' => ['subject', 'title', 'label', 'name'],
          'types' => ['field_item:text' => []],
        ],
      ],
      'content' => [
        'label' => $this->t('Content'),
        'description' => $this->t('Content of the feed item.'),
        'suggested' => ['body'],
        'suggestions' => [
          'targets' => ['body'],
          'types' => ['field_item:text_with_summary' => []],
        ],
      ],
      'description' => [
        'label' => $this->t('Description'),
        'description' => $this->t('Description of the feed item.'),
        'suggested' => ['body'],
        'suggestions' => [
          'targets' => ['body'],
          'types' => ['field_item:text_with_summary' => []],
        ],
      ],
      'author_name' => [
        'label' => $this->t('Author name'),
        'description' => $this->t("Name of the feed item's author."),
        'suggestions' => [
          'types' => ['entity_reference_field' => ['target_type' => 'user']],
        ],
      ],
      'author_email' => [
        'label' => $this->t('Author email'),
        'description' => $this->t("Name of the feed item's email address."),
      ],
      'timestamp' => [
        'label' => $this->t('Published date'),
        'description' => $this->t('Published date as UNIX time GMT of the feed item.'),
        'suggestions' => ['targets' => ['created']],
      ],
      'url' => [
        'label' => $this->t('Item URL (link)'),
        'description' => $this->t('URL of the feed item.'),
        'suggestions' => ['targets' => ['url']],
      ],
      'guid' => [
        'label' => $this->t('Item GUID'),
        'description' => $this->t('Global Unique Identifier of the feed item.'),
        'suggestions' => ['targets' => ['guid']],
      ],
      'tags' => [
        'label' => $this->t('Categories'),
        'description' => $this->t('An array of categories that have been assigned to the feed item.'),
        'suggestions' => [
          'targets' => ['field_tags'],
          'types' => ['field_item:taxonomy_term_reference' => []],
        ],
      ],
      'georss_lat' => [
        'label' => $this->t('Item lattitude'),
        'description' => $this->t('The feed item lattitutde.'),
      ],
      'georss_lon' => [
        'label' => $this->t('Item longitude'),
        'description' => $this->t('The feed item longitude.'),
      ],
      'enclosures' => [
        'label' => $this->t('Enclosures'),
        'description' => $this->t('A list of enclosures attached to the feed item.'),
        'suggestions' => ['types' => ['field_item:file' => []]],
      ],
    ];
  }

}
