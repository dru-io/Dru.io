<?php

namespace Drupal\feeds\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Wrapper methods for \Symfony\Component\EventDispatcher\EventDispatcherInterface.
 *
 * If the class is capable of injecting services from the container, it should
 * inject the 'event_dispatcher' service and assign it to
 * $this->eventDispatcher.
 *
 * @see \Symfony\Component\EventDispatcher\EventDispatcherInterface
 */
trait EventDispatcherTrait {

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $_eventDispatcher;

  /**
   * Dispatches an event.
   *
   * @param string $event_name
   *   The name of the event.
   * @param \Symfony\Component\EventDispatcher\Event $event
   *
   * @return \Symfony\Component\EventDispatcher\Event
   *   The invoked event.
   */
  protected function dispatchEvent($event_name, Event $event = NULL) {
    return $this->getEventDispatcher()->dispatch($event_name, $event);
  }

  /**
   * Returns the event dispatcher service.
   *
   * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
   *   The event dispatcher service.
   */
  protected function getEventDispatcher() {
    if (!isset($this->_eventDispatcher)) {
      $this->_eventDispatcher = \Drupal::service('event_dispatcher');
    }
    return $this->_eventDispatcher;
  }

  /**
   * Sets the event dispatcher service to use.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The string translation service.
   */
  public function setEventDispatcher(EventDispatcherInterface $event_dispatcher) {
    $this->_eventDispatcher = $event_dispatcher;
  }

}
