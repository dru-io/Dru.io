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
 *   id = "druusers",
 *   title = @Translation("DruUser"),
 *   description = @Translation("Parce Terms JSON")
 * )
 */
class UsersParcer extends PluginBase implements ParserInterface {

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
        $item->set('guid', 'user-' . $raw['uid']);
        $item->set('id', 'user-' . $raw['uid']);
        $item->set('name', $raw['name']);
        $item->set('mail', $raw['mail']);
        $item->set('created', $raw['created']);
        $item->set('access', $raw['access']);
        $item->set('login', $raw['login']);
        $item->set('status', $raw['status']);
        $item->set('timezone', $raw['timezone']);
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
      'name'    => ['label' => $this->t('name')],
      'mail'    => ['label' => $this->t('mail')],
      'created' => ['label' => $this->t('created')],
      'access'  => ['label' => $this->t('access')],
      'login'   => ['label' => $this->t('login')],
      'status'   => ['label' => $this->t('status')],
      'timezone' => ['label' => $this->t('timezone')],
      'path'     => ['label' => $this->t('path')],
    ];
  }

}
