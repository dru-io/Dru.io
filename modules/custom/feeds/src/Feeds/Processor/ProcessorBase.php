<?php

namespace Drupal\feeds\Feeds\Processor;

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\PluginBase;
use Drupal\feeds\Plugin\Type\Processor\ProcessorInterface;
use Drupal\feeds\StateInterface;

/**
 * Defines a base processor plugin class.
 */
abstract class ProcessorBase extends PluginBase implements ProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function postProcess(FeedInterface $feed, StateInterface $state) {
    $tokens = [
      '@item' => $this->getItemLabel(),
      '@items' => $this->getItemLabelPlural(),
    ];

    if ($state->created) {
      $state->setMessage($this->formatPlural($state->created, 'Created @count @item.', 'Created @count @items.', $tokens));
    }
    if ($state->updated) {
      $state->setMessage($this->formatPlural($state->updated, 'Updated @count @item.', 'Updated @count @items.', $tokens));
    }
    if ($state->failed) {
      $state->setMessage($this->formatPlural($state->failed, 'Failed importing @count @item.', 'Failed importing @count @items.', $tokens), 'error');
    }
    if (!$state->created && !$state->updated && !$state->failed) {
      $state->setMessage($this->t('There are no new @items.', $tokens));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postClear(FeedInterface $feed, StateInterface $state) {
    $tokens = [
      '@item' => $this->getItemLabel(),
      '@items' => $this->getItemLabelPlural(),
      '%title' => $feed->label(),
    ];

    if ($state->deleted) {
      $state->setMessage($this->formatPlural($state->deleted, 'Deleted @count @item from %title.', 'Deleted @count @items from %title.', $tokens));
    }
    else {
      $state->setMessage($this->t('There are no @items to delete.', $tokens));
    }
  }

}
