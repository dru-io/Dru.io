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
 *   id = "drunodesevent",
 *   title = @Translation("DruNodeEventParcer"),
 *   description = @Translation("Parce Terms JSON")
 * )
 */
class NodeEventParcer extends PluginBase implements ParserInterface {

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
        $item->set('guid', 'node-' . $raw['nid']);
        $item->set('id', 'node-' . $raw['nid']);
        $item->set('title', $raw['title']);
        $item->set('uuid', $raw['info']['uuid']);
        $item->set('type', $raw['info']['type']);
        $item->set('status', $raw['info']['status']);
        $item->set('promote', $$raw['info']['promote']);
        $item->set('sticky', $raw['info']['sticky']);
        $item->set('created', $raw['info']['created']);
        $item->set('changed', $raw['info']['changed']);
        $item->set('uid', 'user-' . $raw['info']['uid']);
        $item->set('uname', $raw['info']['uname']);
        $item->set('body', $raw['fields']['body']['value']);
        $item->set('path', $raw['info']['path']);

        $item->set('field_event_place', $raw['fields']['field_event_place']);
        $item->set('field_event_date', $raw['fields']['field_event_date']);
        $poster = FALSE;
        if (isset($raw['refs']['field_event_poster']['uri'])) {
          $uri = $raw['refs']['field_event_poster']['uri'];
          $poster = str_replace('public://', 'http://dru.io/sites/default/files/', $uri);
        }
        $item->set('field_event_poster', $poster);
        $item->set('field_event_photo', []);
        $item->set('field_event_city', self::refField($raw, 'field_event_city', 'single'));

        $result->addItem($item);
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public static function refField($raw, $name, $single = FALSE) {
    if ($single) {
      $result = FALSE;

      if (isset($raw['refs'][$name]['type'])) {
        $value = $raw['refs'][$name];
        if ($value['type'] == 'cities') {
          $result = $value['name'];
        }
        else {
          $result = 'node-' . $value['id'];
        }

      }
      else {
        $fields[] = 'druio-' . $raw['id'];
      }
      return $result;
    }
    $fields = [];
    if (isset($raw['refs'][$name]) && !empty($raw['refs'][$name])) {
      foreach ($raw['refs'][$name] as $key => $value) {
        if (isset($value['type'])) {
          $fields[] = 'node-' . $value['id'];
        }
        else {
          $fields[] = 'druio-' . $value['id'];
        }
      }
    }
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [
      'guid'    => ['label' => $this->t('guid')],
      'id'      => ['label' => $this->t('id')],
      'uuid'    => ['label' => $this->t('uuid')],
      'type'    => ['label' => $this->t('type')],
      'title'   => ['label' => $this->t('title')],
      'status'  => ['label' => $this->t('status')],
      'promote' => ['label' => $this->t('promote')],
      'sticky'  => ['label' => $this->t('sticky')],
      'created' => ['label' => $this->t('created')],
      'changed' => ['label' => $this->t('changed')],
      'uid'     => ['label' => $this->t('uid')],
      'uname'   => ['label' => $this->t('uname')],
      'body'    => ['label' => $this->t('body')],
      'path'    => ['label' => $this->t('path')],

      'field_event_place'  => ['label' => $this->t('field_event_place')],
      'field_event_city'   => ['label' => $this->t('field_event_city')],
      'field_event_date'   => ['label' => $this->t('field_event_date')],
      'field_event_poster' => ['label' => $this->t('field_event_poster')],
      'field_event_photo'  => ['label' => $this->t('field_event_photo Not Ready')],

    ];
  }

}
