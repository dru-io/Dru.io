<?php

namespace Drupal\feeds;

use Drupal\feeds\Event\ExpireEvent;
use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Event\InitEvent;
use Drupal\feeds\FeedInterface;

/**
 * Expires the items of a feed.
 */
class FeedExpireHandler extends FeedHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function startBatchExpire(FeedInterface $feed) {
    try {
      $feed->lock();
    }
    catch (LockException $e) {
      drupal_set_message(t('The feed became locked before the expiring could begin.'), 'warning');
      return;
    }
    $feed->clearStates();

    $ids = $feed->getType()->getProcessor()->getExpiredIds($feed);

    if (!$ids) {
      $feed->unlock();
      return;
    }

    $batch = [
      'title' => $this->t('Expiring: %title', ['%title' => $feed->label()]),
      'init_message' => $this->t('Expiring: %title', ['%title' => $feed->label()]),
      'progress_message' => $this->t('Expiring: %title', ['%title' => $feed->label()]),
      'error_message' => $this->t('An error occored while expiring %title.', ['%title' => $feed->label()]),
    ];

    foreach ($ids as $id) {
      $batch['operations'][] = [[$this, 'expireItem'], [$feed, $id]];
    }
    $batch['operations'][] = [[$this, 'postExpire'], [$feed]];

    batch_set($batch);
  }


  /**
   * {@inheritdoc}
   */
  public function expireItem(FeedInterface $feed, $item_id) {
    try {
      $this->dispatchEvent(FeedsEvents::INIT_EXPIRE, new InitEvent($feed));
      $this->dispatchEvent(FeedsEvents::EXPIRE, new ExpireEvent($feed, $item_id));
    }
    catch (\RuntimeException $e) {
      drupal_set_message($exception->getMessage(), 'error');
      $feed->clearStates();
      $feed->unlock();
    }
    catch (\Exception $e) {
      $feed->clearStates();
      $feed->unlock();
      throw $e;
    }
  }

  public function postExpire(FeedInterface $feed) {
    $state = $feed->getState(StateInterface::EXPIRE);
    if ($state->total) {
      drupal_set_message(t('Expired @count items.', ['@count' => $state->total]));
    }
    $feed->clearStates();
    $feed->save();
    $feed->unlock();
  }

}
