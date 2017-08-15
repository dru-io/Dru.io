<?php

namespace Drupal\feeds\EventSubscriber;

use Drupal\feeds\Event\ClearEvent;
use Drupal\feeds\Event\ExpireEvent;
use Drupal\feeds\Event\FeedsEvents;
use Drupal\feeds\Event\FetchEvent;
use Drupal\feeds\Event\InitEvent;
use Drupal\feeds\Event\ParseEvent;
use Drupal\feeds\Event\ProcessEvent;
use Drupal\feeds\Plugin\Type\ClearableInterface;
use Drupal\feeds\StateInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener that registers Feeds plugins as event listeners.
 */
class LazySubscriber implements EventSubscriberInterface {

  /**
   * Wether the import listeners have been added.
   *
   * @var array
   */
  protected $importInited = [];

  /**
   * Wether the clear listeners have been added.
   *
   * @var bool
   */
  protected $clearInited = FALSE;

  /**
   * Wether the expire listeners have been added.
   *
   * @var bool
   */
  protected $expireInited = FALSE;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[FeedsEvents::INIT_IMPORT][] = 'onInitImport';
    $events[FeedsEvents::INIT_CLEAR][] = 'onInitClear';
    $events[FeedsEvents::INIT_EXPIRE][] = 'onInitExpire';
    return $events;
  }

  /**
   * Adds import plugins as event listeners.
   */
  public function onInitImport(InitEvent $event, $event_name, EventDispatcherInterface $dispatcher) {
    $stage = $event->getStage();

    if (isset($this->importInited[$stage])) {
      return;
    }
    $this->importInited[$stage] = TRUE;

    switch ($stage) {
      case 'fetch':
        $dispatcher->addListener(FeedsEvents::FETCH, function(FetchEvent $event) {
          $feed = $event->getFeed();
          $result = $feed->getType()->getFetcher()->fetch($feed, $feed->getState(StateInterface::FETCH));
          $event->setFetcherResult($result);
        });
        break;

      case 'parse':
        $dispatcher->addListener(FeedsEvents::PARSE, function(ParseEvent $event) {
          $feed = $event->getFeed();

          $result = $feed
            ->getType()
            ->getParser()
            ->parse($feed, $event->getFetcherResult(), $feed->getState(StateInterface::PARSE));
          $event->setParserResult($result);
        });
        break;

      case 'process':
        $dispatcher->addListener(FeedsEvents::PROCESS, function(ProcessEvent $event) {
          $feed = $event->getFeed();
          $feed
            ->getType()
            ->getProcessor()
            ->process($feed, $event->getParserResult(), $feed->getState(StateInterface::PROCESS));
        });
        break;
    }
  }

  /**
   * Adds clear plugins as event listeners.
   */
  public function onInitClear(InitEvent $event, $event_name, EventDispatcherInterface $dispatcher) {
    if ($this->clearInited === TRUE) {
      return;
    }
    $this->clearInited = TRUE;

    foreach ($event->getFeed()->getType()->getPlugins() as $plugin) {
      if (!$plugin instanceof ClearableInterface) {
        continue;
      }

      $dispatcher->addListener(FeedsEvents::CLEAR, function(ClearEvent $event) use ($plugin) {
        $feed = $event->getFeed();
        $plugin->clear($feed, $feed->getState(StateInterface::CLEAR));
      });
    }
  }

  /**
   * Adds expire plugins as event listeners.
   */
  public function onInitExpire(InitEvent $event, $event_name, EventDispatcherInterface $dispatcher) {
    if ($this->expireInited === TRUE) {
      return;
    }
    $this->expireInited = TRUE;

    $dispatcher->addListener(FeedsEvents::EXPIRE, function(ExpireEvent $event) {
      $feed = $event->getFeed();
      $state = $feed->getState(StateInterface::EXPIRE);

      $feed->getType()
        ->getProcessor()
        ->expireItem($feed, $event->getItemId(), $state);

      $feed->saveStates();
    });
  }

}
