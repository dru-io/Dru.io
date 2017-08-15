<?php

namespace Drupal\feeds\EventSubscriber;

use Drupal\Core\Url as CoreUrl;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\feeds\Component\HttpHelpers;
use Drupal\feeds\Event\DeleteFeedsEvent;
use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Event\FetchEvent;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Result\FetcherResultInterface;
use Drupal\feeds\Result\HttpFetcherResultInterface;
use Drupal\feeds\SubscriptionInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener for PubSubHubbub subscriptions.
 */
class PubSubHubbub implements EventSubscriberInterface {

  /**
   * The subscription storage controller.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a PubSubHubbub object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->storage = $entity_type_manager->getStorage('feeds_subscription');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[FeedsEvents::FETCH][] = ['onPostFetch', FeedsEvents::AFTER];
    $events[FeedsEvents::FEEDS_DELETE][] = 'onDeleteMultipleFeeds';
    return $events;
  }

  /**
   * Subscribes to a feed.
   */
  public function onPostFetch(FetchEvent $event) {
    $feed = $event->getFeed();
    $fetcher = $feed->getType()->getFetcher();

    $subscription = $this->storage->load($feed->id());

    if (!$fetcher->getConfiguration('use_pubsubhubbub')) {
      return $this->unsubscribe($feed, $subscription);
    }

    if (!$hub = $this->findRelation($event->getFetcherResult(), 'hub')) {
      $hub = $fetcher->getConfiguration('fallback_hub');
    }

    // No hub found.
    if (!$hub) {
      return $this->unsubscribe($feed, $subscription);
    }

    // Used to make other URLs absolute.
    $source_url = Url::fromString($feed->getSource());

    $hub = (string) $source_url->combine($hub);

    // If there is a rel="self" relation.
    if ($topic = $this->findRelation($event->getFetcherResult(), 'self')) {
      $topic = (string) $source_url->combine($topic);
      $feed->setSource($topic);
    }
    else {
      $topic = $feed->getSource();
    }

    // Subscription does not exist yet.
    if (!$subscription) {
      $subscription = $this->storage->create([
        'fid' => $feed->id(),
        'topic' => $topic,
        'hub' => $hub,
      ]);

      return $this->subscribe($feed, $subscription);
    }

    if ($topic !== $subscription->getTopic() || $subscription->getHub() !== $hub || $subscription->getState() !== 'subscribed') {
      // Unsubscribe from the old feed.
      $this->unsubscribe($feed, $subscription);

      $subscription = $this->storage->create([
        'fid' => $feed->id(),
        'topic' => $topic,
        'hub' => $hub,
      ]);

      return $this->subscribe($feed, $subscription);
    }
  }

  protected function subscribe(FeedInterface $feed, SubscriptionInterface $subscription) {
    $subscription->subscribe();

    $batch = [
      'title' => t('Subscribing to: %title', ['%title' => $feed->label()]),
      'init_message' => t('Subscribing to: %title', ['%title' => $feed->label()]),
      'operations' => [
        ['Drupal\feeds\EventSubscriber\PubSubHubbub::runSubscribeBatch', [$subscription]],
      ],
      'progress_message' => t('Subscribing: %title', ['%title' => $feed->label()]),
      'error_message' => t('An error occored while subscribing to %title.', ['%title' => $feed->label()]),
    ];

    batch_set($batch);
  }

  protected function unsubscribe(FeedInterface $feed, SubscriptionInterface $subscription = NULL) {
    if (!$subscription) {
      return;
    }

    $subscription->unsubscribe();

    $batch = [
      'title' => t('Unsubscribing from: %title', ['%title' => $feed->label()]),
      'init_message' => t('Unsubscribing from: %title', ['%title' => $feed->label()]),
      'operations' => [
        ['Drupal\feeds\EventSubscriber\PubSubHubbub::runSubscribeBatch', [$subscription]],
      ],
      'progress_message' => t('Unsubscribing: %title', ['%title' => $feed->label()]),
      'error_message' => t('An error occored while unsubscribing from %title.', ['%title' => $feed->label()]),
    ];

    batch_set($batch);
  }

  public static function runSubscribeBatch(SubscriptionInterface $subscription) {
    switch ($subscription->getState()) {
      case 'subscribing':
        $mode = 'subscribe';
        break;

      case 'unsubscribing':
        $mode = 'unsubscribe';
        // The subscription has been deleted, store it for a bit to handle the
        // response.
        $id = $subscription->getToken() . ':' . $subscription->id();
        \Drupal::keyValueExpirable('feeds_push_unsubscribe')->setWithExpire($id, $subscription, 3600);
        break;

      default:
        throw new \LogicException('A subscription was found in an invalid state.');
    }

    $args = [
      'feeds_subscription_id' => $subscription->id(),
      'feeds_push_token' => $subscription->getToken(),
    ];
    $callback = CoreUrl::fromRoute('entity.feeds_feed.subscribe', $args, ['absolute' => TRUE])->toString();

    $post_body = [
      'hub.callback' => $callback,
      'hub.mode' => $mode,
      'hub.topic' => $subscription->getTopic(),
      'hub.secret' => $subscription->getSecret(),
    ];

    $response = static::retry($subscription, $post_body);

    // Response failed.
    if (!$response || $response->getStatusCode() != 202) {
      switch ($subscription->getState()) {
        case 'subscribing':
          // Deleting the subscription will make it re-subscribe on the next
          // import.
          $subscription->delete();
          break;

        case 'unsubscribing':
          // Unsubscribe failed. The hub should give up eventually.
          break;
      }
    }
  }

  /**
   * Retries a POST request.
   *
   * @param \Drupal\feeds\SubscriptionInterface $subscription
   *   The subscription.
   * @param array $body
   *   The POST body.
   * @param int $retries
   *   (optional) The number of retries. Defaults to 3.
   *
   * @return \GuzzleHttp\Message\Response
   *   The Guzzle response.
   */
  protected static function retry(SubscriptionInterface $subscription, array $body, $retries = 3) {
    $tries = 0;

    do {

      $tries++;

      try {
        return \Drupal::httpClient()->post($subscription->getHub(), ['body' => $body]);
      }
      catch (RequestException $e) {
        \Drupal::logger('feeds')->warning('Subscription error: %error', ['%error' => $e->getMessage()]);
      }
    } while ($tries <= $retries);
  }

  /**
   * Finds a hub from a fetcher result.
   *
   * @param \Drupal\feeds\Result\FetcherResultInterface $fetcher_result
   *   The fetcher result.
   *
   * @return string|null
   *   The hub URL or null if one wasn't found.
   */
  protected function findRelation(FetcherResultInterface $fetcher_result, $relation) {
    if ($fetcher_result instanceof HttpFetcherResultInterface) {
      if ($rel = HttpHelpers::findLinkHeader($fetcher_result->getHeaders(), $relation)) {
        return $rel;
      }
    }

    return HttpHelpers::findRelationFromXml($fetcher_result->getRaw(), $relation);
  }

  /**
   * Deletes subscriptions when feeds are deleted.
   */
  public function onDeleteMultipleFeeds(DeleteFeedsEvent $event) {
    $subscriptions = $this->storage->loadMultiple(array_keys($event->getFeeds()));

    foreach ($event->getFeeds() as $feed) {
      if (!isset($subscriptions[$feed->id()])) {
        continue;
      }

      $this->unsubscribe($feed, $subscriptions[$feed->id()]);
    }
  }

}
