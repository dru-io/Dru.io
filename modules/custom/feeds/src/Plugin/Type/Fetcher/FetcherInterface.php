<?php

namespace Drupal\feeds\Plugin\Type\Fetcher;

use Drupal\feeds\FeedInterface;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;
use Drupal\feeds\StateInterface;

/**
 * Interface for Feeds fetchers.
 */
interface FetcherInterface extends FeedsPluginInterface {

  /**
   * Fetch content from a feed and return it.
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed to fetch results for.
   * @param \Drupal\feeds\StateInterface $state
   *   The state object.
   *
   * @return \Drupal\feeds\Result\FetcherResultInterface
   *   A fetcher result object.
   */
  public function fetch(FeedInterface $feed, StateInterface $state);

}
