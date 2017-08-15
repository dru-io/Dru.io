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
 *   id = "drunodes",
 *   title = @Translation("DruNodes"),
 *   description = @Translation("Parce Terms JSON")
 * )
 */
class NodeParcer extends PluginBase implements ParserInterface {

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
        $type = FALSE;
        $k++;
        if ($raw['refs']['field_tour_type']['id'] == 14) {
          $type = 'Автобусные туры на море';
        }
        if ($raw['refs']['field_tour_type']['id'] == 13) {
          $type = 'Автобусные туры из Вологды';
        }
        $status = FALSE;
        if ($raw['fields']['field_tour_status'] == 'открыто бронирование') {
          $status = 'open';
        }

        $timestamp = [];
        if (strtotime($raw['fields']['field_tour_date']['value']) > 0) {
          $timestamp = strtotime($raw['fields']['field_tour_date']['value']);
        }

        $image = FALSE;
        if (!empty($raw['fields']['field_tour_img'])) {
          $image = $raw['fields']['field_tour_img']['uri'];
          $pub = 'http://www.belkatour.ru/sites/default/files/';
          $image = str_replace('public://', $pub, $image);
        }

        $item = new DynamicItem();
        $item->set('guid', $raw['nid']);
        $item->set('id', $raw['nid']);
        $item->set('title', $raw['title']);
        $item->set('status', $raw['info']['status']);
        $item->set('weight', $raw['fields']['field_weight']);
        $item->set('date1', $raw['fields']['field_tour_date']['value']);
        $item->set('date2', $raw['fields']['field_tour_date']['value2']);
        $item->set('route', $raw['fields']['field_tour_route']);
        $item->set('length', $raw['fields']['field_tour_length']);
        $item->set('price', $raw['fields']['field_tour_price']);
        $item->set('status', $raw['fields']['field_tour_status']);
        $item->set('image', $image);
        $item->set('bus_type', $raw['fields']['field_tour_bus_type']);
        $item->set('bus_seats', $raw['fields']['field_tour_bus_seats']);
        $item->set('hotels', $raw['fields']['field_tour_hotels']);
        $item->set('tour_status', $status);
        $item->set('type', $type);
        $item->set('timestamp', $timestamp);
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
      'title'  => ['label' => $this->t('title')],
      'status' => ['label' => $this->t('status')],
      'type'   => ['label' => $this->t('Type Title')],
      'weight' => ['label' => $this->t('weight')],
      'date1'  => ['label' => $this->t('date1')],
      'date2'  => ['label' => $this->t('date2')],
      'timestamp'  => ['label' => $this->t('timestamp')],
      'route'  => ['label' => $this->t('route')],
      'length' => ['label' => $this->t('length')],
      'price'  => ['label' => $this->t('price')],
      'status' => ['label' => $this->t('status')],
      'image'  => ['label' => $this->t('image')],
      'bus_type' => ['label' => $this->t('bus_type')],
      'bus_seats' => ['label' => $this->t('bus_seats')],
      'hotels' => ['label' => $this->t('hotels')],
      'tour_status' => ['label' => $this->t('tour_status')],
    ];
  }

}
