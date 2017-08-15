<?php

namespace Drupal\feeds\Feeds\Item;

/**
 * Defines an item class for use with an RSS/Atom parser.
 */
class SitemapItem extends BaseItem {

  protected $url;
  protected $lastmod;
  protected $changefreq;
  protected $priority;

}
