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
 *   id = "drucommentcomment",
 *   title = @Translation("DruCommentCommentParcer"),
 *   description = @Translation("Parce Terms JSON")
 * )
 */
class CommentCommentParcer extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    // Set time zone to GMT for parsing dates with strtotime().
    $result = new ParserResult();
    $json = trim($fetcher_result->getRaw());
    $raws  = Json::decode($json);
    $k = 0;
    if (!empty($raws)) {
      // $raws = array_reverse($raws);
      foreach ($raws as $key => $raw) {
        $k++;
        $item = new DynamicItem();
        if (!$raw['name']) {
          //$raw['uid'] = 1;
          //$raw['name'] = 'admin';
        }

        if (TRUE) {
          $raw['path'] = "{$raw['cid']}|{$raw['nid']}|{$raw['pid']}";
        }
        if ($k > 0 && $k < 50000) {
          $item->set('guid', 'comment-' . $raw['cid']);
          $item->set('id', 'comment-' . $raw['cid']);
          $item->set('pid', 'node-' . $raw['pid']);
          $item->set('nid', 'cnode-' . $raw['nid']);
          $item->set('uid', 'user-' . $raw['uid']);
          $item->set('subject', $raw['subject']);
          $item->set('hostname', $raw['hostname']);
          $item->set('created', $raw['created']);
          $item->set('changed', $raw['changed']);
          $item->set('status', $raw['status']);
          $item->set('thread', $raw['thread']);
          $item->set('name', $raw['name']);
          $item->set('body', $raw['body']);
          $item->set('path', $raw['path']);
          $item->set('entity', 'comment');
          $item->set('field', 'field_comment');
          $result->addItem($item);
        }
      }
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingSources() {
    return [
      'guid'     => ['label' => $this->t('guid')],
      'id'       => ['label' => $this->t('id')],
      'pid'      => ['label' => $this->t('pid')],
      'nid'      => ['label' => $this->t('nid')],
      'uid'      => ['label' => $this->t('uid')],
      'subject'  => ['label' => $this->t('subject')],
      'hostname' => ['label' => $this->t('hostname')],
      'created'  => ['label' => $this->t('created')],
      'changed'  => ['label' => $this->t('changed')],
      'status'   => ['label' => $this->t('status')],
      'thread'   => ['label' => $this->t('thread')],
      'name'     => ['label' => $this->t('name')],
      'body'     => ['label' => $this->t('body')],
      'path'     => ['label' => $this->t('path')],
      'entity'  => ['label' => $this->t('entity')],
      'field'   => ['label' => $this->t('field')],
    ];

  }

}
