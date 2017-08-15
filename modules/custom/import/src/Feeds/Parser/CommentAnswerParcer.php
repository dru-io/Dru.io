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
 *   id = "drucommentanswer",
 *   title = @Translation("DruCommentAnswerParcer"),
 *   description = @Translation("Parce Terms JSON")
 * )
 */
class CommentAnswerParcer extends PluginBase implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse(FeedInterface $feed, FetcherResultInterface $fetcher_result, StateInterface $state) {
    // Set time zone to GMT for parsing dates with strtotime().
    $result = new ParserResult();
    $json = trim($fetcher_result->getRaw());
    $raws  = Json::decode($json);

    if (!empty($raws)) {
      //$raws = array_reverse($raws);
      foreach ($raws as $key => $raw) {
        $item = new DynamicItem();
        $item->set('guid', 'node-' . $raw['nid']);
        $item->set('id', 'node-' . $raw['nid']);
        $item->set('title', $raw['title']);
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

        $item->set('field_answer_question_reference', self::refField($raw, 'field_answer_question_reference', 'single'));
        $item->set('entity', 'node');
        $item->set('field', 'field_comment');

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

      if (isset($raw['refs'][$name])) {
        $value = $raw['refs'][$name];
        if (TRUE) {
          $result = 'node-' . $value['nid'];
        }
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

      'field_answer_question_reference'    => ['label' => $this->t('field_answer_question_reference')],
      'entity'  => ['label' => $this->t('entity')],
      'field'   => ['label' => $this->t('field')],
    ];
  }

}
