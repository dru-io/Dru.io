<?php

namespace Drupal\feeds\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Session\UserSession;
use Drupal\feeds\Event\EventDispatcherTrait;
use Drupal\feeds\Exception\EmptyFeedException;
use Drupal\feeds\FeedInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base class for Feed queue workers.
 */
abstract class FeedQueueWorkerBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  use EventDispatcherTrait;

  /**
   * The account switcher.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * The queue factory.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Constructs a FeedQueueWorkerBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   The queue factory.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The account switcher.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, QueueFactory $queue_factory, EventDispatcherInterface $event_dispatcher, AccountSwitcherInterface $account_switcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->queueFactory = $queue_factory;
    $this->setEventDispatcher($event_dispatcher);
    $this->accountSwitcher = $account_switcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('queue'),
      $container->get('event_dispatcher'),
      $container->get('account_switcher')
    );
  }

  /**
   * Handles an import exception.
   */
  protected function handleException(FeedInterface $feed, \Exception $exception) {
    $feed->finishImport();

    if (!$exception instanceof EmptyFeedException) {
      throw $exception;
    }
  }

  /**
   * Safely switches to another account.
   *
   * @see \Drupal\Core\Session\AccountSwitcherInterface::switchTo()
   *
   * @param \Drupal\feeds\FeedInterface $feed
   *   The feed that has the account to switch to.
   *
   * @return \Drupal\Core\Session\AccountSwitcherInterface
   *   The account switcher to call switchBack() on.
   */
  protected function switchAccount(FeedInterface $feed) {
    return $this->accountSwitcher->switchTo(new UserSession(['uid' => $feed->getOwnerId()]));
  }

}
