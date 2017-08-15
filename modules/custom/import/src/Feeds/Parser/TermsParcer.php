<?php

namespace Drupal\import\Feeds\Parser;

/**
 * @file
 * Contains \Drupal\cmlparser\Feeds\Parser\CmlProductParser.
 */

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Item\DynamicItem;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Plugin\Type\Parser\ParserInterface;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\ParserResult;
use Drupal\feeds\StateInterface;
use Drupal\Component\Serialization\Json;

/**
 * Defines a CmlProductParser feed parser.
 *
 * @FeedsParser(
 *   id = "druterms",
 *   title = @Translation("DruTerms"),
 *   description = @Translation("Parce Terms JSON")
 * )
 */
class TermsParcer extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    // Set time zone to GMT for parsing dates with strtotime().
    $result = new ParserResult();
    $json = trim($fetcher_result->getRaw());
    $raws  = Json::decode($json);

    if (!empty($raws)) {
      foreach (array_reverse($raws) as $key => $raw) {
        $item = new DynamicItem();
        $item->set('guid', 'druio-' . $raw['tid']);
        $item->set('id', 'druio-' . $raw['tid']);
        $item->set('name', $raw['name']);
        $item->set('weight', $raw['weight']);
        $item->set('parent', $raw['parent']);
        $item->set('pid', $raw['pid']);
        $item->set('path', $raw['path']);
        $result->addItem($item);
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [
      'guid'   => ['label' => $this->t('guid')],
      'id'     => ['label' => $this->t('id')],
      'name'   => ['label' => $this->t('name')],
      'parent' => ['label' => $this->t('parent')],
      'pid'    => ['label' => $this->t('pid')],
      'weight' => ['label' => $this->t('weight')],
      'path'   => ['label' => $this->t('path')],
    ];
  }

}
