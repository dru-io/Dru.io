<?php

namespace Drupal\feeds\Utility;

use GuzzleHttp\Psr7\Uri;
use Zend\Feed\Reader\FeedSet;
use Zend\Feed\Reader\Reader;

/**
 * Helper functions for dealing with feeds.
 */
class Feed {

  /**
   * Discovers RSS or Atom feeds from a document.
   *
   * If the document is an HTML document, this attempts to discover RSS or Atom
   * feeds referenced from the page.
   *
   * @param string $url
   *   The URL of the document.
   * @param string $document
   *   The document to find feeds in. Either an HTML or XML document.
   *
   * @return string|false
   *   The discovered feed, or false if a feed could not be found.
   */
  public static function getCommonSyndication($url, $document) {
    // If this happens to be a feed then just return the url.
    if (static::isFeed($document)) {
      return $url;
    }

    return static::findFeed($url, $document);
  }

  /**
   * Returns if the provided $content_type is a feed.
   *
   * @param string $document
   *   The actual HTML or XML document from the HTTP request.
   *
   * @return bool
   *   Returns true if this is a parsable feed, false if not.
   */
  public static function isFeed($data) {
    Reader::setExtensionManager(\Drupal::service('feed.bridge.reader'));

    try {
      $feed_type = Reader::detectType($data);
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $feed_type != Reader::TYPE_ANY;
  }

  /**
   * Finds potential feed tags in an HTML document.
   *
   * @param string $url
   *   The URL of the document, to use as a base URL.
   * @param string $html
   *   The HTML document to search.
   *
   * @return string|false
   *   The URL of the first feed link found, or false if unable to find a link.
   */
  public static function findFeed($url, $html) {
    $use_error = libxml_use_internal_errors(TRUE);
    $entity_loader = libxml_disable_entity_loader(TRUE);

    $dom = new \DOMDocument();
    $status = $dom->loadHTML(trim($html));

    libxml_disable_entity_loader($entity_loader);
    libxml_use_internal_errors($use_error);

    if (!$status) {
      return FALSE;
    }

    $feed_set = new FeedSet();
    $feed_set->addLinks($dom->getElementsByTagName('link'), $url);

    // Load the first feed type found.
    foreach (['atom', 'rss', 'rdf'] as $feed_type) {
      if (isset($feed_set->$feed_type)) {
        return $feed_set->$feed_type;
      }
    }

    return FALSE;
  }

  /**
   * Translates the scheme of feed-type URLs into HTTP.
   *
   * @param string $url
   *   The URL to translate.
   *
   * @return string
   *   The URL with the scheme translated.
   *
   * @throws \InvalidArgumentException
   *   Thrown then the URL contains an invalid scheme.
   */
  public static function translateSchemes($url) {
    $uri = new Uri($url);

    switch ($uri->getScheme()) {
      case 'http':
      case 'feed':
      case 'webcal':
        return (string) $uri->withScheme('http');

      case 'https':
      case 'feeds':
      case 'webcals':
        return (string) $uri->withScheme('https');
    }

    throw new \InvalidArgumentException();
  }

}
