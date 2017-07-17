<?php

namespace Drupal\feeds\Event;

/**
 * Defines events for the Feeds module.
 */
final class FeedsEvents {

  /**
   * Before plugin executes priority.
   */
  const BEFORE = 10000;

  /**
   * After plugin executes priority.
   */
  const AFTER = -10000;

  /**
   * Fired when one or more feeds are deleted.
   */
  const FEEDS_DELETE = 'feeds.delete_multiple';

  /**
   * Fired before an import begins.
   */
  const INIT_IMPORT = 'feeds.init_import';

  /**
   * Fired when fetching has started.
   */
  const FETCH = 'feeds.fetch';

  /**
   * Fired when parsing has started.
   */
  const PARSE = 'feeds.parse';

  /**
   * Fired when processing has started.
   */
  const PROCESS = 'feeds.process';

  /**
   * Fired before clearing begins.
   */
  const INIT_CLEAR = 'feeds.init_clear';

  /**
   * Fired when clearing has started.
   */
  const CLEAR = 'feeds.clear';

  /**
   * Fired before expiring has started.
   */
  const INIT_EXPIRE = 'feeds.init_expire';

  /**
   * Fired when expiring has started.
   */
  const EXPIRE = 'feeds.expire';

}
