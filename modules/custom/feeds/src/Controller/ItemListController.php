<?php

namespace Drupal\feeds\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Controller\ControllerBase;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\Processor\EntityProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Lists the feed items belonging to a feed.
 */
class ItemListController extends ControllerBase {

  /**
   * Lists the feed items belonging to a feed.
   */
  public function listItems(FeedInterface $feeds_feed, Request $request) {
    $processor = $feeds_feed->getType()->getProcessor();

    $header = [
      'title' => $this->t('Label'),
      'imported' => $this->t('Imported'),
      'guid' => [
        'data' => $this->t('GUID'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'url' => [
        'data' => $this->t('URL'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];

    $build = [];
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => [],
      '#empty' => $this->t('There are no items yet.'),
    ];

    // @todo Allow processors to create their own entity listings.
    if (!$processor instanceof EntityProcessorInterface) {
      return $build;
    }

    $entity_ids = \Drupal::entityQuery($processor->entityType())
    ->condition('feeds_item.target_id', $feeds_feed->id())
    ->pager(50)
    ->sort('feeds_item.imported', 'DESC')
    ->execute();

    $storage = $this->entityTypeManager()->getStorage($processor->entityType());
    foreach ($storage->loadMultiple($entity_ids) as $entity) {
      $ago = \Drupal::service('date.formatter')->formatInterval(REQUEST_TIME - $entity->get('feeds_item')->imported);
      $row = [];

      // Entity link.
      $row[] = [
        'data' => $entity->link(Unicode::truncate($entity->label(), 75, TRUE, TRUE)),
        'title' => $entity->label(),
      ];
      // Imported ago.
      $row[] = $this->t('@time ago', ['@time' => $ago]);
      // Item GUID.
      $row[] = [
        'data' => Html::escape(Unicode::truncate($entity->get('feeds_item')->guid, 30, FALSE, TRUE)),
        'title' => $entity->get('feeds_item')->guid,
      ];
      // Item URL.
      $row[] = [
        'data' => Html::escape(Unicode::truncate($entity->get('feeds_item')->url, 30, FALSE, TRUE)),
        'title' => $entity->get('feeds_item')->url,
      ];

      $build['table']['#rows'][] = $row;
    }

    $build['pager'] = ['#type' => 'pager'];
    $build['#title'] = $this->t('%title items', ['%title' => $feeds_feed->label()]);

    return $build;
  }

}
