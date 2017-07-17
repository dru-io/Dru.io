<?php

namespace Drupal\feeds\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
use Drupal\feeds\SubscriptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns responses for PuSH module routes.
 */
class SubscriptionController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The key value expirable factory.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface
   */
  protected $keyValueExpireFactory;

  /**
   * Constructs a SubscriptionController object.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface $key_value_expire_factory
   *   The key value expirable factory.
   */
  public function __construct(KeyValueExpirableFactoryInterface $key_value_expire_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->keyValueExpireFactory = $key_value_expire_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('keyvalue.expirable'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Handles subscribe/unsubscribe requests.
   *
   * @param int $feeds_subscription_id
   *   The subscription entity id.
   * @param string $feeds_push_token
   *   The subscription token.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return Symfony\Component\HttpFoundation\Response
   *   The response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown if the subscription was not found, or if the request is invalid.
   */
  public function subscribe($feeds_subscription_id, $feeds_push_token, Request $request) {
    // This is an invalid request.
    if ($request->query->get('hub_challenge') === NULL || $request->query->get('hub_topic') === NULL) {
      throw new NotFoundHttpException();
    }

    // A subscribe request.
    if ($request->query->get('hub_mode') === 'subscribe') {
      return $this->handleSubscribe((int) $feeds_subscription_id, $feeds_push_token, $request);
    }

    // An unsubscribe request.
    if ($request->query->get('hub_mode') === 'unsubscribe') {
      return $this->handleUnsubscribe((int) $feeds_subscription_id, $feeds_push_token, $request);
    }

    // Whatever.
    throw new NotFoundHttpException();
  }

  /**
   * Handles a subscribe request.
   *
   * @param int $subscription_id
   *   The subscription entity id.
   * @param string $token
   *   The subscription token.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response challenge.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown if anything seems amiss.
   */
  protected function handleSubscribe($subscription_id, $token, Request $request) {
    if (!$subscription = $this->entityTypeManager()->getStorage('feeds_subscription')->load($subscription_id)) {
      throw new NotFoundHttpException();
    }

    if ($subscription->getToken() !== $token || $subscription->getTopic() !== $request->query->get('hub_topic')) {
      throw new NotFoundHttpException();
    }

    if ($subscription->getState() !== 'subscribing' && $subscription->getState() !== 'subscribed') {
      throw new NotFoundHttpException();
    }

    if ($lease_time = $request->query->get('hub_lease_seconds')) {
      $subscription->setLease($lease_time);
    }

    $subscription->setState('subscribed');
    $subscription->save();

    return new Response(Html::escape($request->query->get('hub_challenge')), 200);
  }

  /**
   * Handles an unsubscribe request.
   *
   * @param int $subscription_id
   *   The subscription entity id.
   * @param string $token
   *   The subscription token.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response challenge.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown if anything seems amiss.
   */
  protected function handleUnsubscribe($subscription_id, $token, Request $request) {
    // The subscription id already deleted, but waiting in the keyvalue store.
    $id = $token . ':' . $subscription_id;

    $subscription = $this->keyValueExpireFactory->get('feeds_push_unsubscribe')->get($id);

    if (!$subscription) {
      throw new NotFoundHttpException();
    }

    $this->keyValueExpireFactory->get('feeds_push_unsubscribe')->delete($id);

    return new Response(Html::escape($request->query->get('hub_challenge')), 200);
  }

  /**
   * Receives a notification.
   *
   * @param \Drupal\feeds\SubscriptionInterface $feeds_subscription
   *   The subscription entity.
   * @param string $feeds_push_token
   *   The subscription token.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return Symfony\Component\HttpFoundation\Response
   *   The response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown if anything seems amiss.
   */
  public function receive(SubscriptionInterface $feeds_subscription, $feeds_push_token, Request $request) {
    if ($feeds_subscription->getToken() !== $feeds_push_token) {
      throw new NotFoundHttpException();
    }

    // X-Hub-Signature is in the format sha1=signature.
    parse_str($request->headers->get('X-Hub-Signature'), $result);

    if (empty($result['sha1']) || !$feeds_subscription->checkSignature($result['sha1'], $request->getContent())) {
      throw new NotFoundHttpException();
    }

    $feed = $this->entityTypeManager()->getStorage('feeds_feed')->load($feeds_subscription->id());

    try {
      $feed->pushImport($request->getContent());
    }
    catch (\Exception $e) {
      return new Response('', 500);
    }

    return new Response('', 200);
  }

}
